<?php

namespace App\Security;

use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class JWTAuthenticator extends AbstractAuthenticator
{
    
    private LoggerInterface $logger;
    private string $projectDir;
    private UserRepository $userRepository;
    private EntityManagerInterface $em;

    public function __construct(LoggerInterface $logger, KernelInterface $kernel, UserRepository $userRepository, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->projectDir = $kernel->getProjectDir();
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    public function supports(Request $request): ?bool
    {
        //return $request->headers->has('Authorization');
        
        return $request->headers->has('Authorization') || $request->cookies->has('jwt_token');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $this->logger->info('[JWTAuthenticator] Ejecutando autenticación');

        //Además del header añades cookis
        $authHeader = $request->headers->get('Authorizartion');
        $token = null;

        if($authHeader && str_starts_with($authHeader, 'Bearer')){
            $token = substr($authHeader, 7);
        }elseif($request->cookies->has('jwt_token')){
            $token = $request->cookies->get('jwt_token');
        }

        if(!$token){
            $this->logger->warning('[JWTAutehnticator] No se encontró el token ni en cabecera ni en cookie');
            throw new AuthenticationException('Token JWT no encontrado');
        }

        $path = $this->projectDir . '/config/jwt/clerk_jwt.json';

        if(!file_exists($path)){
            //$this->logger->error('No se encuentran archivos en JWKS en: $path');
            throw new \RuntimeException('Archivos jwks no encontrado en path');
        }

        try{
            $jwks = json_decode(file_get_contents($path), true);
            $keys = JWK::parseKeySet($jwks);

            $decoded = JWT::decode($token, $keys);
            $this->logger->info('[DEBUG] Payload JWT completo: ' . json_encode($decoded));


            $clerkId = $decoded->sub;
            //$this->logger->info('JWT decodificado correctamente', ['sub' => $clerkId]);
            $email = $decoded->email ?? null;
            $username = $decoded->username ?? null;
            $firstName = $decoded->first_name ?? null;
            $lastName = $decoded->last_name ?? null;
            $imageUrl = $decoded->image_url ?? null;

            $this->logger->info('[AWTAuthenticator] Decodificado token', [
                'clerkId' => $clerkId,
                'email' => $email,
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
            
            
             return new SelfValidatingPassport(new UserBadge($clerkId, function ($userIdentifier) use($email, $username, $firstName, $lastName, $imageUrl) {
                $user = $this->userRepository->findOneBy(['clerkId' => $userIdentifier]);
                
                //Validar si el email existe para que no lance error sql
                if(!$user){
                    if($email){
                        $user = $this->userRepository->findOneBy(['email' => $email]);

                        if($user){
                            $this->logger->info('[JWTAuthenticator] Usuario encontrado por email, actualizando ClerkId');
                            $user->setClerkId($userIdentifier);
                            $this->em->flush();
                        }
                    }
                }

                if (!$user) {
                    $this->logger->info('[JWTAuthenticator] Usuario no encontrado. Creando nuevo usuario con ClerkId: ' . $userIdentifier);

                    $user = new User();
                    $user->setClerkId($userIdentifier);
                    $user->setEmail($email ?? $userIdentifier . '@clerk.dev');
                    $user->setUserName($username ?? 'user_' . substr($userIdentifier, -6));
                    $user->setName($firstName ?? 'Nombre');
                    $user->setFirstLastName($lastName ?? 'Apellido1');
                    $user->setSecondLastName(''); 
                    $user->setPassword('');
                    $user->setPhoto($imageUrl);
                    $user->setPhone('000000000');
                    $user->setRoles(['ROLE_USER']);
                    
                    $this->em->persist($user);
                    $this->em->flush();
                    
                    $this->logger->info('[JWTAuthenticator] Usuario creado exitosamente: ' . $user->getEmail());
                }
                
                return $user;
            }));
        
        } catch (\Exception $e) {
            $this->logger->error('[JWTAuthenticator] Error al procesar el token: ' . $e->getMessage());
            throw new AuthenticationException('Token inválido: ' . $e->getMessage());
        }



    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?JsonResponse
    {
        return null; // Permite el acceso
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?JsonResponse
    {
        return new JsonResponse(['error' => $exception->getMessage()], 401);
    }
        
}


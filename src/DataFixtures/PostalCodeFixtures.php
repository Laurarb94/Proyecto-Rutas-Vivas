<?php

namespace App\DataFixtures;

use App\Entity\Localities;
use App\Entity\PostalCode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Output\ConsoleOutput;

class PostalCodeFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {

        //Cargas los códios postales del archivo CSV
        $file = fopen(__DIR__ . '/../../public/csv/postalcat.csv', 'r');

        $localitiesProcessed = [];

        //leer el archivo
        while(($line = fgetcsv($file, 0, ";")) !==FALSE){
            $postcode = trim($line[1]); //código postal
            $localityName = trim($line[3]); //localidad

            //ver si la localidad está en la bbdd
            $locality = $manager->getRepository(Localities::class)->findOneBy(['name' => $localityName]);

            //Si no existe, la procesamos directamente
            if($locality){
                $localitiesProcessed[] = $localityName;

                //Crear el código postal
                $postcodeEntity = new PostalCode();
                $postcodeEntity-> setNumPostalCode($postcode);
                $postcodeEntity->setLocality($locality);

                $manager->persist($postcodeEntity);
            }
        }

        fclose($file);

        $manager->flush();

        $output = new ConsoleOutput();
        $output->writeln("Códigos postales importados correctamente.");

    }











}
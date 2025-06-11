---------------------------------------------------------------------------------------------------------------------

RUTAS VIVAS 🌍🌿

Rutas vivas es una aplicación web desarrollada en Symfony que permite a los usuarios registrar, compartir y explorar actividades 
deportivas, al mismo tiempo que conectar usuarios. 

Este proyecto ha sido realizado durante mis prácticas de Grado Superior de Aplicaciones Web, a media jornada, en la empresa "Segundoplano: Soluciones informáticas y Producción Audiovisual".

-----------------------------------------------------------------------------------------------------------------------------

✨ FUNCIONALIDADES PRINCIPALES
- Registro y edición del perfil del usuario
- Creación, edición y eliminación de actividades
- Búsqueda y seguimiento de usuarios
- Sistema de notificaciones por eventos sociales
- Edición de contraseña y gestión de la cuenta
- Interfaz visual intuitiva y amigable

-------------------------------------------------------------------------------------------------------------------------

🌄 CAPTURAS DE PANTALLA CON DESCRIPCIÓN

🏠 INICIO 

Pantalla de bienvenida con descripción de la aplicación y botones para iniciar sesión o registrarse

![1](https://github.com/user-attachments/assets/ccdcaffb-378a-4628-99e3-c4064ad347a8)

Breve explicación de las funciones clave: explorar rutas, compartir aventuras y conectar con otras personas

![2](https://github.com/user-attachments/assets/b67560b1-b45d-4b0b-ab57-125723aa1a19)

--------------------------------------------------------------------------------------------------------------

👤 PERFIL DEL USUARIO

Vista del perfil con foto, biografía, actividades completadas y explorador de actividades

![3](https://github.com/user-attachments/assets/66253059-0f51-4054-ac5b-41bcddda9102)

Sección de datos personales del usuario, para poder editar sólo el dato que necesite y poder ver el resto 

![datos](https://github.com/user-attachments/assets/1fe6f659-8f43-4073-b731-c05a3399b204)

Selección de la categoría con sus subcategorías correspondientes

![4](https://github.com/user-attachments/assets/abc2881c-e499-4773-8b99-abd62dd30ce6)

-----------------------------------------------------------------------------------------------------------------------

➕ CREAR UNA ACTIVIDAD

Formulario para registrar una actividad: categoría y subcategoría ya viene seleccionada del paso anterior, opción para subir
una foto, seleccionar un acompañante mediante autocompletado, añadir una valoración, un comentario y la ubicación. 

![5](https://github.com/user-attachments/assets/e7a537b5-3307-4dd8-a989-ff130c46f893)

![6](https://github.com/user-attachments/assets/fd5c2410-b592-402e-98f1-38ce625d9cf5)

------------------------------------------------------------------------------------------------------------------------

📝 VISTA DE LA ACTIVIDAD Y EDICIÓN 

Detalles completos de una actviidad registrada: tipo, fecha, foto, acompañante, comentario, valoración y ubicación

![7](https://github.com/user-attachments/assets/f68520c0-e9a7-4334-8b77-f57bde027424)

Formulario para editar una actividad

![9](https://github.com/user-attachments/assets/4ce044f2-a7b8-4178-8fc7-8de029e78d33)

----------------------------------------------------------------------------------------------------------------------

🔔 NOTIFICACIONES

Sistema de notificaciones: solicitudes recibidas, aceptadas o enviadas

![10](https://github.com/user-attachments/assets/61fdcc76-72f9-4012-99af-a65a965a638e)

------------------------------------------------------------------------------------------------------------------------

👥 CONEXIONES SOCIALES

Vista de "personas que podrías conocer" con botones para enviar una solicitud de seguimiento

![11](https://github.com/user-attachments/assets/c45449c2-f62c-4b79-9f75-f5c97e163698)

Listado de amigos

![12](https://github.com/user-attachments/assets/921c8dc8-c529-44ab-8a8f-52487fc41cdd)

Vista del perfil de otro usuario con su biografía y actividades finalizadas, si las tiene, si son amigos

![13](https://github.com/user-attachments/assets/f247c252-631e-445b-a142-44a4ac8879ae)

Panel de solicitudes enviadas con estado "pendiente"

![14](https://github.com/user-attachments/assets/d5655b3c-7041-4a80-874f-50f06bde2baf)

-------------------------------------------------------------------------------------------------------------------------

🔐 Configuración de la cuenta

Formulario para actualizar la contraseña desde el perfil

![15](https://github.com/user-attachments/assets/b8698213-32e8-4fc2-822a-924a79807ea0)

----------------------------------------------------------------------------------------------------------------------

💪 TECNOLOGÍAS UTILIZADAS

- Symfony (framework PHP)
- Twig (motor de plantillas)
- JavaScript Vanilla (interactividad frontend)
- Doctrine ORM (base de datos)
- Bootstrap UX/UI responsive design con estilos personalizados

----------------------------------------------------------------------------------------------------------------------------

🚀 INSTALACIÓN LOCAL

git clone https://github.com/Laurarb94/Proyecto-Rutas-Vivas.git

cd Proyecto-Rutas-Vivas

composer install

npm install && npm run build

Crear un archivo .env con variables como:

DATABASE_URL=...

MAILER_DSN=...

php bin/console doctrine:database:create

php bin/console doctrine:migrations:migrate

php bin/console doctrine:fixtures:load

symfony server:start

-------------------------------------------------------------------------------------------------------------------------

👩‍💻 AUTORA Y CONTACTO

Laura Rodríguez Basanta

Desarrolladora web junior

670 795 831

laurarodriguezbasanta@gmail.com

[LinkedIn](https://www.linkedin.com/in/laura-rodriguez-basanta/)

----------------------------------------------------------------------------------------------------------------------------------------------
 📄 LICENCIA
 
 Todos los derechos reservados. Proyecto realizado durante mis prácticas formativas. No autorizado el uso comercial sin consentimiento.

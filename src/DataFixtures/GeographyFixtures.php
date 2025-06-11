<?php

namespace App\DataFixtures;

use App\Entity\Provinces;
use App\Entity\Localities;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Output\ConsoleOutput;

class GeographyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Cargar las provincias y localidades desde el archivo CSV
        $file = fopen(__DIR__ . '/../../public/csv/localidades.csv', 'r');
        if ($file === false) {
            throw new \Exception("No se pudo abrir el archivo localidades.csv.");
        }

        //Array para guardar las provincias ya procesadas y que no las repita millones de veces
        $provincesProcessed = [];

        // Leer el archivo CSV
        while (($line = fgetcsv($file, 0, ";")) !== FALSE) {

            $provinceName = trim($line[1]); // Columna de la provincia
            $localityName = trim($line[2]); // Columna de la localidad

            //Si la provincia ya ha sido procesada, la salta
            if(isset($provincesProcessed[$provinceName])){
                $province = $provincesProcessed[$provinceName];
            }else{
                // Comprobar si la provincia ya está en la base de datos
                $province = $manager->getRepository(Provinces::class)->findOneBy(['name' => $provinceName]);

                // Si la provincia no existe en la base de datos, la creamos
                if (!$province) {
                    // Crear la provincia solo si no existe
                    $province = new Provinces();
                    $province->setName($provinceName);
                    $manager->persist($province);  // Persistir la nueva provincia
                }

                //Guardar la provincia en el array para no procesarla de nuevo
                $provincesProcessed[$provinceName] = $province;
            }

            // Crear la localidad
            $locality = new Localities();
            $locality->setName($localityName);
            $locality->setProvince($province);  // Asociar la provincia correctamente a la localidad

            // Persistir la localidad
            $manager->persist($locality);
        }

        fclose($file);

        // Flush para guardar todo de una vez en la base de datos
        $manager->flush();

        // Imprimir un mensaje de éxito
        $output = new ConsoleOutput();
        $output->writeln("Provincias y localidades importadas correctamente.");
    }
}

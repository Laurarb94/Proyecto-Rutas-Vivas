<?php

namespace App\DataFixtures;

use App\Entity\CategoryActivity;
use App\Entity\SubcategoryActivity;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {

        $categoriesData =
            [
                'Aventura' => [
                    'Ala delta',
                    'Barranquismo',
                    'Paracaidismo',
                    'Parapente',
                    'Puenting',
                    'Tirolesa (zip line)',
                ],
                'Bicicross' => [
                    'Bicicross de competición',
                    'Bicicross de montaña',
                ],
                'Carreras' => [
                    'Carreras de obstáculos',
                    'Orientación',
                    'Running urbano',
                    'Trail running',
                    'Ultra trail',
                ],
                'Ciclismo' => [
                    'BMX',
                    'Bicicleta de carretera',
                    'Ciclocross',
                    'Gravel',
                    'Mountain Bike (MTB)',
                ],
                'Deportes acuáticos' => [
                    'Bodyboard',
                    'Kayak',
                    'Kitesurf',
                    'Paddle surf (SUP)',
                    'Rafting',
                    'Snorkel',
                    'Submarinismo (buceo)',
                    'Surf',
                    'Windsurf',
                ],
                'Deportes aéreos' => [
                    'Paracaidismo',
                    'Parapente',
                    'Paramotor',
                    'Salto BASE',
                    'Vuelo en globo',
                ],
                'Deportes urbanos' => [
                    'Breakdance deportivo',
                    'Calistenia',
                    'Crossfit outdoor',
                    'Parkour',
                    'Slackline',
                ],
                'Equitación' => [
                    'Doma clásica',
                    'Equitación de salto',
                    'Raid ecuestre',
                    'Rutas a caballo',
                ],
                'Escalada' => [
                    'Boulder',
                    'Escalada artificial',
                    'Escalada deportiva',
                    'Escalada en hielo',
                    'Escalada en roca',
                ],
                'Escapadas activas' => [
                    'Campamentos deportivos',
                    'Multiaventura',
                    'Surfcamps',
                    'Team building outdoor',
                ],
                'Esquí y nieve' => [
                    'Esquí alpino',
                    'Esquí de fondo',
                    'Esquí de travesía',
                    'Freestyle',
                    'Snowboard',
                    'Splitboard',
                ],
                'Montañismo' => [
                    'Alpinismo',
                    'Ascensiones técnicas',
                    'Esquí de travesía',
                    'Raquetas de nieve',
                ],
                'Natación' => [
                    'Natación en piscina',
                    'Natación sincronizada',
                    'Natacion en aguas abiertas',
                    'Waterpolo',
                ],
                'Náuticos y navegación' => [
                    'Esquí acuático',
                    'Flyboard',
                    'Motonáutica',
                    'Vela',
                    'Windsurf',
                ],
                'Patinaje' => [
                    'Patinaje artístico',
                    'Patinaje en hierba',
                    'Patinaje en línea',
                    'Patinaje sobre hielo',
                    'Roller derby',
                    'Speed skating',
                ],
                'Puenting' => [
                    'Puenting en caída libre',
                    'Puenting en salto',
                ],
                'Senderismo' => [
                    'Marcha nórdica',
                    'Senderismo de montaña',
                    'Senderismo en la naturaleza',
                    'Senderismo nocturno',
                    'Trekking',
                ],
                'Skate' => [
                    'Cruising',
                    'Downhill',
                    'Park',
                    'Street',
                    'Vert',
                ],
                'Tiro y precisión' => [
                    'Airsoft',
                    'Paintball',
                    'Tiro con arco',
                    'Tiro deportivo',
                ],
            ];


        foreach ($categoriesData as $categoryName => $subCategories){
            //creas la categoría
            $category = new CategoryActivity();
            $category->setName($categoryName);
            $manager->persist($category);

            //Creas las subcategorías
            foreach($subCategories as $subCategoryName){
                $subCategory = new SubcategoryActivity();
                $subCategory->setName($subCategoryName);
                $subCategory->setCategoryActivity($category);
                $manager->persist($subCategory);
            }

        }

        $manager->flush();

    }

}





<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'title' => 'Allgemeine Demografie',
                'children' => [
                    ['title' => 'Geschlecht'],
                    ['title' => 'Ethnische Zugehörigkeit'],
                    ['title' => 'Alter'],
                    ['title' => 'Einkommensniveau'],
                    ['title' => 'Bildungsniveau'],
                ],
            ],
            [
                'title' => 'Gesundheit und Lebensstil',
                'children' => [
                    ['title' => 'Rauchgewohnheiten'],
                    ['title' => 'Alkoholkonsum'],
                    ['title' => 'Ernährungsgewohnheiten'],
                    ['title' => 'Bewegungsgewohnheiten'],
                    ['title' => 'Schlafmuster'],
                ],
            ],
            [
                'title' => 'Arbeit und Beruf',
                'children' => [
                    ['title' => 'Berufsstatus'],
                    ['title' => 'Branchenzugehörigkeit'],
                    ['title' => 'Arbeitszufriedenheit'],
                    ['title' => 'Arbeitsplatzsicherheit'],
                    ['title' => 'Arbeitsstunden'],
                ],
            ],
            [
                'title' => 'Technologie und Medien',
                'children' => [
                    ['title' => 'Internetnutzung'],
                    ['title' => 'Social - Media - Nutzung'],
                    ['title' => 'Fernsehgewohnheiten'],
                    ['title' => 'Online - Kaufverhalten'],
                    ['title' => 'Technologiepräferenzen'],
                ],
            ],
            [
                'title' => 'Umweltbewusstsein',
                'children' => [
                    ['title' => 'Recyclingverhalten'],
                    ['title' => 'Nutzung erneuerbarer Energien'],
                    ['title' => 'Einstellung zum Klimawandel'],
                    ['title' => 'Verkehrsmittelwahl'],
                    ['title' => 'Kauf von umweltfreundlichen Produkten'],
                ],
            ],
            [
                'title' => 'Politik und Gesellschaft',
                'children' => [
                    ['title' => 'Parteizugehörigkeit'],
                    ['title' => 'Wahlverhalten'],
                    ['title' => 'Meinung zu aktuellen politischen Themen'],
                    ['title' => 'Vertrauen in Regierungsinstitutionen'],
                    ['title' => 'Bürgerrechtsfragen'],
                ],
            ],
            [
                'title' => 'Freizeit und Hobbys',
                'children' => [
                    ['title' => 'Sportliche Aktivitäten'],
                    ['title' => 'Kulturelle Veranstaltungen'],
                    ['title' => 'Reisepräferenzen'],
                    ['title' => 'Lesegewohnheiten'],
                    ['title' => 'Hobbys und Interessen'],
                ],
            ],
            [
                'title' => 'Finanzen und Wirtschaft',
                'children' => [
                    ['title' => 'Sparverhalten'],
                    ['title' => 'Investitionspräferenzen'],
                    ['title' => 'Schuldenstand'],
                    ['title' => 'Kaufverhalten'],
                    ['title' => 'Einstellung zur Wirtschaftslage'],
                ],
            ],
            [
                'title' => 'Familie und Beziehungen',
                'children' => [
                    ['title' => 'Familienstand'],
                    ['title' => 'Kinderzahl'],
                    ['title' => 'Partnerschaftsstatus'],
                    ['title' => 'Familienplanung'],
                    ['title' => 'Beziehungszufriedenheit'],
                ],
            ],
            [
                'title' => 'Glaube und Spiritualität',
                'children' => [
                    ['title' => 'Religiöse Zugehörigkeit'],
                    ['title' => 'Kirchenbesuchsfrequenz'],
                    ['title' => 'Glaubensüberzeugungen'],
                    ['title' => 'Spirituelle Praktiken'],
                    ['title' => 'Einstellung zu Religion in der Gesellschaft'],
                ],
            ],
            [
                'title' => 'Wohnsituation',
                'children' => [
                    ['title' => 'Wohnort'],
                    ['title' => 'Wohnungsgröße'],
                    ['title' => 'Wohnungsart'],
                    ['title' => 'Wohnungsausstattung'],
                    ['title' => 'Wohnungsmiete'],
                ],
            ],
            [
                'title' => 'Einstellung und Persönlichkeit',
                'children' => [
                    ['title' => 'Persönlichkeitstyp'],
                    ['title' => 'Werte und Überzeugungen'],
                    ['title' => 'Einstellung zu sozialen Themen'],
                    ['title' => 'Einstellung zu moralischen Themen'],
                    ['title' => 'Einstellung zu ethischen Themen'],
                ],
            ],
            [
                'title' => 'Pr0gramm',
                'children' => [],
            ],
        ];

        foreach ($categories as $category) {
            $categoryModel = Category::create(['title' => $category['title']]);
            foreach ($category['children'] as $child) {
                Category::create([
                    'title' => $child['title'],
                    'parent_id' => $categoryModel->getKey(),
                ]);
            }
        }
    }
}

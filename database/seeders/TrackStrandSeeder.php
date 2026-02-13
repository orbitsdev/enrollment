<?php

namespace Database\Seeders;

use App\Models\Track;
use Illuminate\Database\Seeder;

class TrackStrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tracks = [
            [
                'name' => 'Academic Track',
                'code' => 'ACAD',
                'sort_order' => 1,
                'strands' => [
                    ['name' => 'STEM', 'code' => 'STEM', 'sort_order' => 1],
                    ['name' => 'ABM', 'code' => 'ABM', 'sort_order' => 2],
                    ['name' => 'HUMSS', 'code' => 'HUMSS', 'sort_order' => 3],
                    ['name' => 'GAS', 'code' => 'GAS', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'TVL Track',
                'code' => 'TVL',
                'sort_order' => 2,
                'strands' => [
                    ['name' => 'Home Economics', 'code' => 'HE', 'course' => null, 'sort_order' => 1],
                    ['name' => 'Information and Communications Technology', 'code' => 'ICT', 'course' => 'Computer System Servicing (NC II)', 'sort_order' => 2],
                    ['name' => 'Agri-Fishery Arts', 'code' => 'AFA', 'course' => null, 'sort_order' => 3],
                ],
            ],
        ];

        foreach ($tracks as $trackData) {
            $strands = $trackData['strands'];
            unset($trackData['strands']);

            $track = Track::create($trackData);

            foreach ($strands as $strandData) {
                $track->strands()->create($strandData);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Cabezal;
use App\Models\Licencia;
use App\Models\Piloto;
use Illuminate\Database\Seeder;

class PilotosPropiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pilotos = [
            ['nombre' => 'ENRIQUE MANOLO REYES ORELLANA', 'licencia' => '3918 86266 1801', 'placa' => 'C-491BXM'],
            ['nombre' => 'VÍCTOR FRANCISCO GUTIÉRREZ RODRÍGUEZ', 'licencia' => '2672 44584 1801', 'placa' => 'C-110BPM'],
            ['nombre' => 'FERNANDO ADÁN BANCES ESCOBAR', 'licencia' => '2973 02205 0101', 'placa' => 'C-111BKR'],
            ['nombre' => 'HENRY ELIU ANTONIO PINTO', 'licencia' => '1602 69784 1801', 'placa' => 'C-653BPY'],
            ['nombre' => 'JOHN HENRY TALLY PALENCIA', 'licencia' => '1823 74009 1801', 'placa' => 'C-294BSW'],
            ['nombre' => 'ANGEL MANOLO RAMIREZ HERNANDEZ', 'licencia' => '2653 83935 1801', 'placa' => 'C-726BKY'],
            ['nombre' => 'CARLOS ROMEO RAMOS VÁSQUEZ', 'licencia' => '1586 95232 2007', 'placa' => 'C-828BZX'],
            ['nombre' => 'HUGO GARCIA RAMIREZ', 'licencia' => '2501 78931 1804', 'placa' => 'C-465BBX'],
            ['nombre' => 'EDGAR RAFAEL ORTIZ VILLAFUERTE', 'licencia' => '2605 43764 0601', 'placa' => 'C-001BPP'],
            ['nombre' => 'ALEJANDRO RAMOS RIAN', 'licencia' => '2185 28027 0502', 'placa' => 'C-962BQM'],
            ['nombre' => 'HAMILTON JONATAN RODAS SOSA', 'licencia' => '3480 38585 0101', 'placa' => 'C-692BPQ'],
            ['nombre' => 'GERZON NEFTALI ALVARADO VARGAS', 'licencia' => '2330 19081 1904', 'placa' => 'C-070BXZ'],
            ['nombre' => 'CÁNDIDO JOSSEPTH MARÍN GAMBOA', 'licencia' => '2534 57181 1801', 'placa' => 'C-864BYK'],
            ['nombre' => 'OSCAR RENE DÍAZ CORTEZ', 'licencia' => '2619 42859 1801', 'placa' => 'C-062BPM'],
            ['nombre' => 'MIGUEL ALEXANDER CASASOLA CHO', 'licencia' => '2585 82367 1801', 'placa' => 'C-242BPC'],
        ];

        foreach ($pilotos as $data) {
            $cabezal = Cabezal::firstOrCreate(['placa' => $data['placa']]);
            $piloto = Piloto::updateOrCreate(
                ['nombre' => $data['nombre']],
                ['cabezal_id' => $cabezal->id],
            );

            Licencia::updateOrCreate(
                ['piloto_id' => $piloto->id],
                ['numero' => $data['licencia']],
            );
        }
    }
}

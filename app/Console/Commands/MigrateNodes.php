<?php

namespace App\Console\Commands;

use App\Models\Edge;
use App\Models\Nodo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MigrateNodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:nodes';
    protected $description = 'Migrar datos de nodos y edges desde un archivo JSON a la base de datos';

    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Cargar el archivo JSON
        $json = File::get(storage_path('app/nodos.json'));
        $data = json_decode($json, true);
        $coordenadas_edificios = [
            "Edificio A" => ['lat' => 20.740475484548387, 'lng' => -103.37718367128555],
            "Edificio B" => ['lat' => 20.740699093013447, 'lng' => -103.37716896170467],
            "Edificio F1" => ['lat' => 20.740560469081302, 'lng' => -103.37711392497734],
            "Edificio C" => ['lat' => 20.740946568688557, 'lng' => -103.37713646203734],
            "Edificio D" => ['lat' => 20.74126051171166, 'lng' => -103.37717060097611],
            "Edificio G" => ['lat' => 20.74201090631346, 'lng' => -103.37608957764458],
            "Edificio H" => ['lat' => 20.741710334835947, 'lng' => -103.37620460611592],
            "Edificio I" => ['lat' => 20.741393943171143, 'lng' => -103.376316251375],
            "Edificio J" => ['lat' => 20.73949481303867, 'lng' => -103.37616261732077],
            "Cruce 4" => ['lat' => 20.740430321500813, 'lng' => -103.37675036469675],
            "Cruce 5" => ['lat' => 20.739799719874483, 'lng' => -103.37559460716544],
            "Cruce 6" => ['lat' => 20.741626624676027, 'lng' => -103.3754409819047],
            "Cruce 7" => ['lat' => 20.741302040281347, 'lng' => -103.3754618445957],
            "Cruce 8" => ['lat' => 20.740175744794065, 'lng' => -103.37557753769305],
            "Cruce 9" => ['lat' => 20.739790804212667, 'lng' => -103.3756422688102],
            "Cruce 10" => ['lat' => 20.739833446961207, 'lng' => -103.37604460013281],
            "Cruce 11" => ['lat' => 20.739938799582532, 'lng' => -103.37683853394279],
            "Cruce 12" => ['lat' => 20.738544597616, 'lng' => -103.37709321605007],
            "Cruce 13" => ['lat' => 20.738873200015227, 'lng' => -103.37835117198546]
        ];

        // Migrar los nodos a la base de datos
        foreach ($data['nodes'] as $node) {
            if (isset($node['lat'])) {
                Nodo::updateOrCreate(
                    ['nombre' => $node['id']],
                    [
                        'nombre' => $node['id'],
                        'edificio' => $node['edificio'],
                        'piso' => $node['piso'],
                        'lat' => $node['lat'],
                        'lng' => $node['lng']
                    ]
                );
            } else {
                if (isset($coordenadas_edificios[$node['edificio']])) {
                    Nodo::updateOrCreate(
                        ['nombre' => $node['id']],
                        [
                            'nombre' => $node['id'],
                            'edificio' => $node['edificio'],
                            'piso' => $node['piso'],
                            'lat' => $coordenadas_edificios[$node['edificio']]['lat'] + (mt_rand() / mt_getrandmax() - 0.5) * 0.00005,
                            'lng' => $coordenadas_edificios[$node['edificio']]['lng'] + (mt_rand() / mt_getrandmax() - 0.5) * 0.00005

                        ]
                    );
                }
            }
        }
        foreach ($data['edges'] as $edge) {
            $fromNode = Nodo::where('nombre', $edge['from'])->first();
            $toNode = Nodo::where('nombre', $edge['to'])->first();
            if ($fromNode && $toNode) {
                $weight = $this->calcularDistancia($fromNode->lat, $fromNode->lng, $toNode->lat, $toNode->lng);
                Edge::updateOrCreate(
                    [
                        'from_node' => $edge['from'],
                        'to_node' => $edge['to']
                    ],
                    ['weight' => $weight]
                );
            }
        }

        $this->info('Datos migrados correctamente.');
    }

    /**
     * Función para calcular la distancia entre dos puntos geográficos
     *
     * @param float $lat1 Latitud del primer punto
     * @param float $lng1 Longitud del primer punto
     * @param float $lat2 Latitud del segundo punto
     * @param float $lng2 Longitud del segundo punto
     * @return float Distancia en metros
     */
    function calcularDistancia($lat1, $lng1, $lat2, $lng2)
    {
        $R = 6371e3; // Radio de la Tierra en metros
        $φ1 = deg2rad($lat1);  // Convertir latitudes y longitudes de grados a radianes
        $φ2 = deg2rad($lat2);
        $Δφ = deg2rad($lat2 - $lat1);  // Diferencia en latitudes
        $Δλ = deg2rad($lng2 - $lng1);  // Diferencia en longitudes

        $a = sin($Δφ / 2) * sin($Δφ / 2) +
            cos($φ1) * cos($φ2) *
            sin($Δλ / 2) * sin($Δλ / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;  // Distancia en metros
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Edge;
use App\Models\Nodo;
use Illuminate\Http\Request;

class MapController extends Controller
{

    public function nodos()
    {
        $nodos = Nodo::orderBy('nombre')->get();
        return response()->json($nodos);
    }

    public function mapa()
    {
        return view("welcome");
    }
    public function ruta(Request $request)
    {
        $startNode = $request->start;  // Nodo de inicio
        $endNode =  $request->end;  // Nodo de destino

        $path = $this->dijkstra($startNode, $endNode);

        if (!$path) {
            return response()->json(['error' => 'No se encontró ruta.'], 404);
        }

        $coordinates = [];
        foreach ($path as $nodeId) {
            $node = Nodo::where('nombre', $nodeId)->first();
            if ($node) {
                $coordinates[] = [
                    'nombre' => $node->nombre,
                    'lat' => $node->lat,
                    'lng' => $node->lng,
                ];
            }
        }

        // Recupera nodos que están en el path
        $nodos = Nodo::whereIn('nombre', $path)->get(['nombre', 'lat', 'lng']);

        // Opcional: también puedes incluir todos los edges si los necesitas
        $edges = Edge::whereIn('from_node', $path)
            ->whereIn('to_node', $path)
            ->get(['from_node as from', 'to_node as to']);

        return response()->json([
            'nodes' => $nodos,
            'edges' => $edges,
            'path' => $path,
        ]);

        //return response()->json($coordinates);
    }
    function dijkstra($startNode, $endNode)
    {
        $nodes = Nodo::all()->keyBy('nombre');
        $edges = Edge::all();

        if (!isset($nodes[$startNode]) || !isset($nodes[$endNode])) {
            return null;
        }

        $adjacencyList = [];

        foreach ($nodes as $nodeName => $node) {
            $adjacencyList[$nodeName] = [];
        }

        foreach ($edges as $edge) {
            $adjacencyList[$edge->from_node][$edge->to_node] = $edge->weight;
            $adjacencyList[$edge->to_node][$edge->from_node] = $edge->weight;
            /*if (strcmp('Puerta 2', $edge->from_node) == 0) {
                echo "nodo conectado a puerta 2 " . $edge->to_node . "<br/>";
            }
            if (strcmp('Puerta 2', $edge->to_node) == 0) {
                echo "nodo conectado a puerta 2 " . $edge->from_node . "<br/>";
            }*/
        }
        $dist = [];
        $prev = [];
        $queue = [];

        foreach ($nodes as $nodeName => $node) {
            $dist[$nodeName] = INF;
            $prev[$nodeName] = null;
            $queue[$nodeName] = true;
        }

        $dist[$startNode] = 0;

        while (!empty($queue)) {
            // Encontrar nodo con menor distancia
            $minNode = null;
            foreach ($queue as $nodeName => $_) {
                if ($minNode === null || $dist[$nodeName] < $dist[$minNode]) {
                    $minNode = $nodeName;
                }
            }

            if ($minNode === null || $dist[$minNode] === INF) {
                break;
            }

            unset($queue[$minNode]);

            if ($minNode === $endNode) {
                break;
            }

            foreach ($adjacencyList[$minNode] as $neighbor => $weight) {
                //echo "Lista de adyacencia:<br/>";
                //print_r($adjacencyList);
                //echo "Evaluando vecino $neighbor desde $minNode con peso $weight <br/>";

                if (!isset($queue[$neighbor])) {
                    //echo "Vecino $neighbor ya visitado <br/>";
                    continue;
                }

                $alt = $dist[$minNode] + $weight;
                //echo "Distancia alternativa para $neighbor: $alt <br/>";

                if ($alt < $dist[$neighbor]) {
                    //  echo "Actualizando distancia para $neighbor: $alt <br/>";
                    $dist[$neighbor] = $alt;
                    $prev[$neighbor] = $minNode;
                }
            }
        }

        // Reconstruir el camino
        $path = [];
        $u = $endNode;

        if ($prev[$u] === null && $u !== $startNode) {
            // No hay ruta
            return null;
        }

        while ($u !== null) {
            array_unshift($path, $u);
            $u = $prev[$u];
        }

        if (empty($path) || $path[0] !== $startNode) {
            return null;
        }

        return $path;
    }
}

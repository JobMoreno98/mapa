<?php

namespace App\Http\Controllers;

use App\Models\Edge;
use App\Models\Nodo;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index()
    {
        $startNodeId = 'Puerta 1';  // Nodo de inicio
        $endNodeId = 'Entrada Edificio A';  // Nodo de destino

        $path = $this->dijkstra($startNodeId, $endNodeId);
        $coordinates = [];

        foreach ($path as $nodeId) {
            $node = Nodo::where('id', $nodeId)->first();  // Usamos `where()` en vez de `find()`
            if ($node) {
                $coordinates[] = [
                    'lat' => $node->lat,
                    'lng' => $node->lng
                ];
            }
        }

        return response()->json($coordinates);  // Devuelve las coordenadas como respuesta JSON
    }

    function dijkstra($startNodeId, $endNodeId)
    {
        // Obtener todos los nodos y aristas de la base de datos
        $nodes = Nodo::all();
        $edges = Edge::all();

        // Crear un mapa de nodos con su latitud y longitud
        $nodesMap = $nodes->pluck('lat', 'id')->toArray();

        // Inicializar distancias
        $distances = [];
        $previous = [];
        $unvisited = [];

        // Inicialización de las distancias y los nodos no visitados
        foreach ($nodes as $node) {
            $distances[$node->id] = INF;  // Establece todas las distancias a infinito
            $previous[$node->id] = null;  // Ningún nodo tiene un predecesor al principio
            $unvisited[$node->id] = true;  // Todos los nodos están sin visitar
        }

        // La distancia desde el nodo de inicio es 0
        $distances[$startNodeId] = 0;
        dd($unvisited);
        while (!empty($unvisited)) {
            // Obtener el nodo con la distancia mínima
            $minNode = null;
            foreach ($unvisited as $nodeId => $_) {
                if ($minNode === null || $distances[$nodeId] < $distances[$minNode]) {
                    $minNode = $nodeId;
                }
            }

            // Si ya hemos alcanzado el nodo destino, podemos parar
            if ($minNode == $endNodeId) {
                break;
            }
            unset($unvisited[$minNode]);
            $outgoingEdges = $edges->where('from_node', $minNode);
            foreach ($outgoingEdges as $edge) {
                $neighbor = $edge->to_node;
                $alt = $distances[$minNode] + $edge->weight;
                if ($alt < $distances[$neighbor]) {
                    $distances[$neighbor] = $alt;
                    $previous[$neighbor] = $minNode;
                    dd($minNode);
                }
            }
        }

        // Reconstruir el camino desde el nodo final hasta el nodo inicial
        $path = [];
        $current = $endNodeId;
        while ($current !== null && $previous[$current] !== null) {
            array_unshift($path, $current);  // Inserta el nodo al principio del array
            $current = $previous[$current];
        }

        if ($current !== null) {
            array_unshift($path, $current);  // Añade el nodo inicial al principio si es válido
        }

        return $path;
    }
}

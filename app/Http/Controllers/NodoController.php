<?php

namespace App\Http\Controllers;

use App\Models\Edge;
use App\Models\Nodo;
use Illuminate\Http\Request;

class NodoController extends Controller
{
    public function create()
    {
        return view("nodos.create");
    }

    public function store(Request $request)
    {
        $request->validate([
            'from' => 'required|string',
            'to' => 'required|string',
            'weight' => 'nullable|numeric'
        ]);

        $from = $request->input('from');
        $to = $request->input('to');
        $weight = $request->input('weight');

        // Si no hay peso, lo calculamos por distancia geográfica
        if (!$weight) {
            $nodoA = Nodo::where('nombre', $from)->first();
            $nodoB = Nodo::where('nombre', $to)->first();

            if (!$nodoA || !$nodoB) {
                return response()->json(['message' => 'Nodos no encontrados'], 404);
            }

            $weight = $this->calcularDistancia($nodoA->lat, $nodoA->lng, $nodoB->lat, $nodoB->lng);
        }

        // Guardar la conexión (ida y vuelta si quieres)
        Edge::updateOrCreate(
            ['from_node' => $from, 'to_node' => $to],
            ['weight' => $weight]
        );

        return response()->json(['message' => 'Nodos unidos correctamente']);
    }

    private function calcularDistancia($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // en metros

        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $deltaLat = $lat2 - $lat1;
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // en metros
    }

    public function crear_nodo()
    {
        return view("nodos.single_nodo");
    }

    public function agregar_nodo(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|unique:nodos,nombre',
            'edificio' => 'required|string',
            'piso' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        Nodo::create([
            'nombre' => $validated['nombre'],
            'edificio' => $validated['edificio'],
            'piso' => $validated['piso'],
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
        ]);

        return response()->json(['message' => 'Nodo agregado exitosamente.']);
    }
}

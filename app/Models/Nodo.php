<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nodo extends Model
{
    protected $table = 'nodos';

    // Especifica que no se usará la propiedad "timestamps"
    public $timestamps = false;

    protected $fillable = ['id', 'nombre', 'edificio', 'piso', 'lat', 'lng'];

    public function edgesFrom()
    {
        return $this->hasMany(Edge::class, 'from_node', 'id');
    }

    public function edgesTo()
    {
        return $this->hasMany(Edge::class, 'to_node', 'id');
    }
}

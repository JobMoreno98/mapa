@extends('layout.app')
@section('cuerpo')
    <div class="container">
        <h4>Añadir Nodo</h4>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form id="formAgregarNodo" class="my-3">
            @csrf
            <div class="mb-2">
                <label for="nombre" class="form-label">Nombre del Nodo:</label>
                <input type="text" class="form-control" name="nombre" id="nombre" required />
            </div>

            <div class="mb-2">
                <label for="edificio" class="form-label">Edificio:</label>
                <input type="text" class="form-control" name="edificio" id="edificio" required />
            </div>

            <div class="mb-2">
                <label for="piso" class="form-label">Piso:</label>
                <input type="text" class="form-control" name="piso" id="piso" placeholder="Ej: PB, P1, P2"
                    required />
            </div>

            <div class="mb-2">
                <label for="lat" class="form-label">Latitud:</label>
                <input type="number" class="form-control" name="lat" id="lat" step="any" required />
            </div>

            <div class="mb-2">
                <label for="lng" class="form-label">Longitud:</label>
                <input type="number" class="form-control" name="lng" id="lng" step="any" required />
            </div>

            <button type="submit" class="btn btn-primary">Guardar Nodo</button>
        </form>

        <div id="respuestaAgregarNodo" class="alert d-none"></div>

    </div>

@endsection
@section('js')
    <script>
        const formNodo = document.getElementById("formAgregarNodo");
        const respuestaNodo = document.getElementById("respuestaAgregarNodo");

        formNodo.addEventListener("submit", (e) => {
            e.preventDefault();

            const data = {
                nombre: document.getElementById("nombre").value,
                edificio: document.getElementById("edificio").value,
                piso: document.getElementById("piso").value,
                lat: parseFloat(document.getElementById("lat").value),
                lng: parseFloat(document.getElementById("lng").value),
            };

            fetch("{{ route('agregar.nodo') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
                    },
                    body: JSON.stringify(data),
                })
                .then((res) => res.json())
                .then((result) => {
                    respuestaNodo.className = "alert alert-success";
                    respuestaNodo.textContent = result.message || "Nodo agregado correctamente.";
                    respuestaNodo.classList.remove("d-none");
                    formNodo.reset();
                })
                .catch((err) => {
                    respuestaNodo.className = "alert alert-danger";
                    respuestaNodo.textContent = "Error al agregar nodo.";
                    respuestaNodo.classList.remove("d-none");
                    console.error(err);
                });
        });
    </script>
@endsection

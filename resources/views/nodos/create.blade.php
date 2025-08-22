@extends('layout.app')
@section('cuerpo')
    <div class="container">
        <h4>Unir Nodos</h4>
        <form id="formUnirNodos" class="my-3">
            @method('POST')
            @csrf
            <div class="mb-2">
                <label for="fromNode" class="form-label">Desde (nodo A):</label>
                <select class="form-select" id="fromNode" required></select>
            </div>
            <div class="mb-2">
                <label for="toNode" class="form-label">Hasta (nodo B):</label>
                <select class="form-select" id="toNode" required></select>
            </div>
            <div class="mb-2">
                <label for="peso" class="form-label">Peso (opcional):</label>
                <input type="number" class="form-control" id="peso" placeholder="Distancia o peso entre nodos"
                    step="0.01" />
            </div>
            <button type="submit" class="btn btn-primary">Unir Nodos</button>
        </form>

        <div id="respuestaUnirNodos" class="alert d-none"></div>
    </div>
@endsection
@section('js')
    <script>
        const form = document.getElementById("formUnirNodos");
        const fromNode = document.getElementById("fromNode");
        const toNode = document.getElementById("toNode");
        const pesoInput = document.getElementById("peso");
        const respuestaDiv = document.getElementById("respuestaUnirNodos");

        // Cargar nodos en los selects
        fetch("{{ route('all.nodes') }}")
            .then((res) => res.json())
            .then((nodos) => {
                nodos.forEach((n) => {
                    const optionA = document.createElement("option");
                    optionA.value = n.nombre;
                    optionA.textContent = n.nombre;

                    const optionB = optionA.cloneNode(true);

                    fromNode.appendChild(optionA);
                    toNode.appendChild(optionB);
                });
            });

        // Manejar el submit

        form.addEventListener("submit", (e) => {
            e.preventDefault();

            const from = fromNode.value;
            const to = toNode.value;
            const peso = pesoInput.value;

            fetch("{{ route('nodos.store') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content ||
                            "", // Laravel CSRF token
                    },
                    body: JSON.stringify({
                        from,
                        to,
                        weight: peso
                    }),
                })
                .then((res) => res.json())
                .then((data) => {
                    respuestaDiv.className = "alert alert-success";
                    respuestaDiv.textContent = data.message || "Nodos unidos correctamente.";
                    respuestaDiv.classList.remove("d-none");
                    form.reset();
                })
                .catch((error) => {
                    respuestaDiv.className = "alert alert-danger";
                    respuestaDiv.textContent = "Error al unir nodos.";
                    respuestaDiv.classList.remove("d-none");
                    console.error(error);
                });
        });
    </script>
@endsection

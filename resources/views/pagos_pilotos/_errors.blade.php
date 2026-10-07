@if ($errors->any())
    <div class="alert danger" role="alert">
        <strong>Revise los datos del formulario:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

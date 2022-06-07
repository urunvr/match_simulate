<table class="table">
    <thead class="thead-dark">
        <tr>
            @foreach ($headers as $header)
                <th class="text-left" scope="col">{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        {{ $slot }}
    </tbody>
</table>


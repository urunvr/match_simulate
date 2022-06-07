<x-app-layout title="Teams">

    <div class="container mt-4">
        <div class="row">
            @foreach ($matches as $key => $match)
                <div class="col-md-4">
                    <table class="table">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-left" scope="col">{{ $key + 1 . '.Week' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($match as $pair)
                                <tr>
                                    <td>{{ $pair[0] }} - {{ $pair[1] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
        <div class="row mt-4">
            <button class="btn btn-info" onclick="window.location='{{ route('show') }}'">
                Start Simulations
            </button>
        </div>

    </div>
</x-app-layout>

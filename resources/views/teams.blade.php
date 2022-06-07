<x-app-layout title="Teams">
    <div class="container mt-4">
        <div class="row">
            <x-table.table :headers="['Team Name']">
                @foreach ($teams as $team)
                    <tr>
                        <td>{{ $team->name }}</td>
                    </tr>
                @endforeach
            </x-table.table>
        </div>
        <div class="row">
            <button class="btn btn-info" onclick="window.location='{{ route('generate_fixtures') }}'">
                Generate Fixtures
            </button>
        </div>

    </div>
</x-app-layout>

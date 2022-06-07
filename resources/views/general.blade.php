<x-app-layout title="Teams">

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-6">
                <table id="point_table" class="table">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-left" scope="col">Team Name</th>
                            <th class="text-center" scope="col">Played</th>
                            <th class="text-center" scope="col">Won</th>
                            <th class="text-center" scope="col">Drawn</th>
                            <th class="text-center" scope="col">Lost</th>
                            <th class="text-center" scope="col">GF</th>
                            <th class="text-center" scope="col">GA</th>
                            <th class="text-center" scope="col">GD</th>
                            <th class="text-center" scope="col">Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teams as $t)
                            <tr>
                                <td class="text-left">{{ $t->name }}</td>
                                <td class="text-center">{{ $t->played }}</td>
                                <td class="text-center">{{ $t->won }}</td>
                                <td class="text-center">{{ $t->drawn }}</td>
                                <td class="text-center">{{ $t->lost }}</td>
                                <td class="text-center">{{ $t->gf }}</td>
                                <td class="text-center">{{ $t->ga }}</td>
                                <td class="text-center">{{ $t->gd }}</td>
                                <td class="text-center">{{ $t->points }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-3">
                <table class="table" id="weeks">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-left" scope="col">{{ 'Week ' . $matches[1] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($matches[0] as $m)
                            <tr>
                                <td>{{ $m[0] }} - {{ $m[1] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-3">
                <table id="prodiction" class="table">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-left" scope="col">Championship Prodictions</th>
                            <th class="text-left" scope="col">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @isset($prodictions)
                            @foreach ($prodictions as $p)
                                <tr>
                                    <td>{{ $p[0] }}</td>
                                    <td>{{ $p[1] }}</td>
                                </tr>
                            @endforeach
                        @endisset
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-4 d-flex justify-content-center">
                <button class="btn btn-info" onclick="simulateLeague()">
                    Start Simulations
                </button>
            </div>
            <div class="col-md-4 d-flex justify-content-center">
                <button class="btn btn-info" onclick="playNextWeek()">
                    Play Next Week
                </button>
            </div>
            <div class="col-md-4 d-flex justify-content-center">
                <button class="btn btn-danger" onclick="resetData()">
                    Reset Data
                </button>
            </div>
        </div>

    </div>

    @section('scripts')
        <script type="text/javascript">
            function playNextWeek() {
                $.ajax({
                    type: 'GET',
                    url: "/play-week",
                    success: function(data) {
                        console.log('first,', data)
                        let week = data.matches[1];
                        let weekMatches = data.matches[0].map(i => {
                            return `<tr>
                                <td>${i[0]} - ${i[1]}</td>
                            </tr>`;
                        });
                        let pointTable = data.teams.map(i => {
                            return `<tr> <td class="text-left">${i.name}</td>
                            <td class="text-center">${i.played}</td>
                            <td class="text-center">${i.won}</td>
                            <td class="text-center">${i.drawn}</td>
                            <td class="text-center">${i.lost}</td>
                            <td class="text-center">${i.gf}</td>
                            <td class="text-center">${i.ga}</td>
                            <td class="text-center">${i.gd}</td>
                            <td class="text-center">${i.points}</td></tr>`
                        })

                        $('#weeks thead').html(`<tr><th class='text-left' scope='col'>Week ${week}</th></tr>`)
                        $('#weeks tbody').html(weekMatches);

                        $('#point_table tbody').html(pointTable);
                        if (data.prodictions) {
                            let prodictions = data.prodictions.map(i => {
                                return `<tr>
                                <td>${i[0]}</td>
                                <td>${i[1]}</td>
                            </tr>`;
                            });
                            $('#prodiction tbody').html(prodictions);
                        }



                    }
                });
            }

            function simulateLeague() {
                $.ajax({
                    type: 'GET',
                    url: "/simulate-league",
                    success: function(data) {
                        let week = data.matches[1];
                        let weekMatches = data.matches[0].map(i => {
                            return `<tr>
                                <td>${i[0]} - ${i[1]}</td>
                            </tr>`;
                        });
                        let pointTable = data.teams.map(i => {
                            return `<tr> <td class="text-left">${i.name}</td>
                            <td class="text-center">${i.played}</td>
                            <td class="text-center">${i.won}</td>
                            <td class="text-center">${i.drawn}</td>
                            <td class="text-center">${i.lost}</td>
                            <td class="text-center">${i.gf}</td>
                            <td class="text-center">${i.ga}</td>
                            <td class="text-center">${i.gd}</td>
                            <td class="text-center">${i.points}</td></tr>`
                        })

                        $('#weeks thead').html(`<tr><th class='text-left' scope='col'>Week ${week}</th></tr>`)
                        $('#weeks tbody').html(weekMatches);

                        $('#point_table tbody').html(pointTable);
                        if (data.prodictions) {
                            let prodictions = data.prodictions.map(i => {
                                return `<tr>
                                <td>${i[0]}</td>
                                <td>${i[1]}</td>
                            </tr>`;
                            });
                            $('#prodiction tbody').html(prodictions);
                        }
                    }
                });
            }

            function resetData() {
                $.ajax({
                    type: 'GET',
                    url: "/reset",
                    success: function(data) {
                        console.log('sdadsa', data)
                        let week = data.matches[1];
                        let weekMatches = data.matches[0].map(i => {
                            return `<tr>
                                <td>${i[0]} - ${i[1]}</td>
                            </tr>`;
                        });
                        let pointTable = data.teams.map(i => {
                            return `<tr> <td class="text-left">${i.name}</td>
                            <td class="text-center">${i.played}</td>
                            <td class="text-center">${i.won}</td>
                            <td class="text-center">${i.drawn}</td>
                            <td class="text-center">${i.lost}</td>
                            <td class="text-center">${i.gf}</td>
                            <td class="text-center">${i.ga}</td>
                            <td class="text-center">${i.gd}</td>
                            <td class="text-center">${i.points}</td></tr>`
                        })

                        $('#weeks thead').html(`<tr><th class='text-left' scope='col'>Week ${week}</th></tr>`)
                        $('#weeks tbody').html(weekMatches);

                        $('#point_table tbody').html(pointTable);
                        if (data.prodictions) {
                            let prodictions = data.prodictions.map(i => {
                                return `<tr>
                                <td>${i[0]}</td>
                                <td>${i[1]}</td>
                            </tr>`;
                            });
                            $('#prodiction tbody').html(prodictions);
                        }
                    }
                });




                // $('#weeks tbody').append("<tr><td>This is row</td></tr>")
            }
        </script>
    @stop

</x-app-layout>

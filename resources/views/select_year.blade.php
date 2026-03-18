<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Grand Livre RSA</title>
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</head>
<body>
<div class="container mt-4">
    <h1>Choix de l'année</h1>
    <p class="text-muted">Année courante : <strong>{{ $current_year }}</strong></p>
    <form method="post" action="{{ route('pickYear') }}">
        @csrf
        <div class="mb-3">
            <label for="year" class="form-label">Année</label>
            <select class="form-select" id="year" name="year">
                @foreach ($years as $year)
                    <option value="{{ $year }}" @selected($year === $current_year)>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Valider</button>
    </form>
</div>
</body>
</html>

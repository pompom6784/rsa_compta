@extends('layout')

@section('body')
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
@endsection

<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Grand Livre RSANav {{ $current_year }}</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="{{ route('home') }}">Home</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="{{ route('book') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Livre
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('book') }}">Livre</a></li>
            <li><a class="dropdown-item" href="{{ route('toBreakdown') }}">A ventiler</a></li>
            <li><a class="dropdown-item" href="{{ route('selectYear') }}">Choix année</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('imports') }}">Imports</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

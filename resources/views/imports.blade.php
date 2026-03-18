@extends('layout')

@section('body')
  @if (!empty($error))
    <div class="alert alert-danger" role="alert">
      {{ $error }}
    </div>
  @endif
  <form method="post" action="{{ route('paypal') }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
      <label for="importPaypal" class="form-label">Import Paypal</label>
      <input type="file" class="form-control" id="importPaypal" name="importPaypal">
      <button type="submit" class="btn btn-primary">Envoyer</button>
    </div>
  </form>
  <form method="post" action="{{ route('sogecom') }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
      <label for="importSogecom" class="form-label">Import Sogecom</label>
      <input type="file" class="form-control" id="importSogecom" name="importSogecom">
      <button type="submit" class="btn btn-primary">Envoyer</button>
    </div>
  </form>
  <form method="post" action="{{ route('sg') }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
      <label for="importSG" class="form-label">Import SG</label>
      <input type="file" class="form-control" id="importSG" name="importSG">
      <button type="submit" class="btn btn-primary">Envoyer</button>
    </div>
  </form>
  <form method="post" action="{{ route('checkDelivery') }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
      <label for="importCheckDelivery" class="form-label">Import Remises de chèques</label>
      <input type="file" class="form-control" id="importCheckDelivery" name="importCheckDelivery">
      <button type="submit" class="btn btn-primary">Envoyer</button>
    </div>
  </form>
@endsection

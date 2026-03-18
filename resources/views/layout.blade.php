<!DOCTYPE html>

<html lang="fr">

<head>

<title>Grand Livre RSA</title>

<meta charset="utf-8">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://cdn.datatables.net/v/dt/jq-3.7.0/dt-2.3.7/fh-4.0.6/datatables.min.css" rel="stylesheet" integrity="sha384-yJrobgt0wFqSJNdxOFcBbOXNpr8I/uFjwZ44/196NWd7rHSf7GYycUBGhz33pyUd" crossorigin="anonymous">
<script src="https://cdn.datatables.net/v/dt/jq-3.7.0/dt-2.3.7/fh-4.0.6/datatables.min.js" integrity="sha384-/YuHOFVXnW0bJqgpLdERyNItcLG4UoQuEXmtCKesNfWDsKuCOQUMYbfaJ+gkmutf" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/date-fns/1.30.1/date_fns.js"></script>
<style>
  #book tfoot input {
    width: 100%;
  }
</style>

@yield('head')

</head>

<body>

@include('navbar')

@yield('container', '<div class="container">')

@yield('body')

</div>

</body>

</html>

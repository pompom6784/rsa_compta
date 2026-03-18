@extends('layout')

@section('container')
<div class="container-fluid">
@endsection

@section('body')
  <table id="book" class="table table-striped" style="width:100%">
    <thead>
      <tr>
        <th></th>
        <th>Type</th>
        <th>Date</th>
        <th>Nom</th>
        <th>Libellé</th>
        <th>Débit</th>
        <th>Crédit</th>
        <th>Ventilation</th>
        <th>Renouv.</th>
        <th>F. Client</th>
        <th>Cot. RSANav</th>
        <th>Cot. RSA</th>
        <th>Suivi Nav</th>
        <th>Rbt PEN</th>
        <th title="Reunion / Seminaire PEN">Reu/Sem Pen</th>
        <th>Frais Paypal</th>
        <th>Frais Sogecom</th>
        <th>OSAC</th>
        <th>Autres Charges</th>
        <th>Dons</th>
        <th>Vib Deb.</th>
        <th>Vib Cred.</th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th></th>
        <th>Type</th>
        <th>Date</th>
        <th>Nom</th>
        <th>Libellé</th>
        <th>Débit</th>
        <th>Crédit</th>
        <th>Ventilation</th>
        <th>Renouv.</th>
        <th>F. Client</th>
        <th>Cot. RSANav</th>
        <th>Cot. RSA</th>
        <th>Suivi Nav</th>
        <th>Rbt PEN</th>
        <th title="Reunion / Seminaire PEN">Reu/Sem Pen</th>
        <th>Frais Paypal</th>
        <th>Frais Sogecom</th>
        <th>OSAC</th>
        <th>Autres Charges</th>
        <th>Dons</th>
        <th>Vib Deb.</th>
        <th>Vib Cred.</th>
      </tr>
    </tfoot>
  </table>
  <div>
    <a href="{{ route('excel') }}" class="btn btn-secondary"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
  </div>

  <script type="text/javascript">
    function moneyFormat(data) {
      if (!data) {
        return '';
      }
      return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(data);
    }
    $('#book').DataTable({
      ajax: {
        url: '{{ route('lines') }}',
        type: "POST",
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
      },
      orderCellsTop: true,
      fixedHeader: true,
      processing: true,
      serverSide: true,
      stateSave: true,
      stateSaveCallback: function (settings, data) {
        localStorage.setItem( 'DataTables', JSON.stringify(data) );
      },
      stateLoadCallback: function (settings) {
        return JSON.parse( localStorage.getItem('DataTables') );
      },
      language: {
        url: '/datatable_fr.json',
      },
      order: [[2, 'asc']],
      pageLength: 20,
      lengthMenu: [
        [10, 20, 50, -1],
        [10, 20, 50, 'Toutes']
      ],
      columns: [
        {
          data: 'id',
          render: function(data, type, row) {
            let url = '{{ route('line', ['id' => 0]) }}';
            url = url.replace('/0', '/' + data);
            return '<a href="'+url+'" style="white-space: nowrap;"><i class="bi bi-pencil-square"></i> Editer</a>';
          },
          sortable: false,
        },
        {data: 'type'},
        {data: 'date',
          render: function(data, type, row) {
            const date = new Date(data.date);
            return dateFns.format(date, 'DD/MM/YYYY');
          }
        },
        {data: 'name'},
        {data: 'label'},
        {
          data: 'debit',
          className: 'dt-right font-weight-bold',
          render : function (data) {
            return '<strong>'+moneyFormat(data)+'</strong>';
          }
        },
        {
          data: 'credit',
          className: 'dt-right font-weight-bold',
          render : function (data) {
            return '<strong>'+moneyFormat(data)+'</strong>';
          }
        },
        {
          data: 'breakdown',
          render: function (data) {
            return data.length > 0 ? '<span style="color: green;">&check;</span>' : '<span style="color: red;">&cross;</span>';
          }
        },
        {
          data: 'breakdownPlaneRenewal',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdownCustomerFees',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdownRSANavContribution',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdownRSAContribution',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdownFollowUpNav',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdownPenRefund',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdownMeeting',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdownPaypalFees',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdownSogecomFees',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdownOsac',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdownOtherFees',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdownDonation',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdownVibrationDebit',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdownVibrationCredit',
          render : function (data) {
            return moneyFormat(data);
          }
        },
      ],
      initComplete: function () {
        this.api()
            .columns()
            .every(function () {
                let column = this;
                let title = column.footer().textContent;

                // Create input element
                let input = document.createElement('input');
                input.placeholder = title;
                column.footer().replaceChildren(input);

                // Event listener for user input
                input.addEventListener('keyup', () => {
                    if (column.search() !== this.value) {
                        column.search(input.value).draw();
                    }
                });
            });
    }
  });
  </script>
@endsection

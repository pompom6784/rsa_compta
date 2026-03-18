@extends('layout')

@section('body')
  <form method="post" class="line-form" action="{{ route('line', ['id' => $line->getId()]) }}">
    @csrf
    @method('POST')
    <div class="mb-3">
      <label for="date" class="form-label">Date</label>
      <div class="form-control">{{ $line->getDate()->format('Y-m-d') }}</div>
    </div>
    <div class="mb-3">
      <label for="name" class="form-label">Banque</label>
      <select class="form-select" name="type">
        <option @selected(!$line->getType())>- Choisir -</option>
        <option value="PAYPAL" @selected($line->getType() === 'PAYPAL')>PAYPAL</option>
        <option value="Sogecom" @selected($line->getType() === 'Sogecom')>Sogecom</option>
        <option value="VRT" @selected($line->getType() === 'VRT')>Virement</option>
        <option value="CHQ" @selected($line->getType() === 'CHQ')>Chèque</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="name" class="form-label">Nom</label>
      <input type="text" class="form-control" id="name" name="name" value="{{ $line->getName() }}">
    </div>
    <div class="mb-3">
      <label for="label" class="form-label">Libellé</label>
      <input type="text" class="form-control" id="label" name="label" value="{{ $line->getLabel() }}">
    </div>
    <div class="mb-3">
      <label for="amount" class="form-label">Montant</label>
      <input type="text" class="form-control" id="amount" name="amount" readonly disabled
             value="{{ number_format($line->getAmount(), 2, ',', ' ') }} €">
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea rows="5" class="form-control" id="description" name="description">{{ $line->getDescription() }}</textarea>
    </div>

    @if ($line->getLabel() === 'REMISES DE CHEQUES')
      <div class="mb-3">
        <label for="description" class="form-label">Remises</label>
        <select class="form-control" size="5" name="check_delivery">
          @foreach ($check_deliveries as $delivery)
            <option
              value="{{ $delivery->getId() }}"
              data-amount="{{ $delivery->getAmount() }}"
              data-count="{{ $delivery->getLines()->count() }}"
            >
              {{ $delivery->getAmount() }} € / {{ $delivery->getDate()->format('Y-m-d') }} / {{ $delivery->getLines()->count() }} chèques
            </option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Pointer</button>
    @else
      <div class="mb-3">
        <label for="breakdown" class="form-label">Ventilation</label>
        <div class="row">
          @foreach ($breakdowns as $value => $name)
            <div class="col-sm-3">
              <div class="form-check">
                <input class="form-check-input breakdown_toggle" type="checkbox"
                       value="{{ $value }}" name="breakdown[{{ $value }}]"
                       id="breakdown_{{ $value }}"
                       @checked(in_array($value, $line->getBreakdown())) />
                <label class="form-check-label" for="breakdown_{{ $value }}">
                  {{ $name }}
                </label>
              </div>
            </div>
          @endforeach
        </div>
      </div>
      <div class="mb-3">
        <div class="row">
          @foreach ($breakdowns as $value => $name)
            <div class="col-sm-3 breakdown-input-{{ $value }}"
                 @unless(in_array($value, $line->getBreakdown())) style="display: none" @endunless>
              <label for="breakdown{{ $value }}" class="form-label">{{ $name }}</label>
              <input type="text" class="form-control breakdown-input"
                     id="breakdown{{ $value }}" name="breakdown{{ $value }}"
                     value="{{ number_format((float) ($line->{'breakdown' . $value} ?? 0), 2, ',', ' ') }} €">
              @if ($value === 'PlaneRenewal')
                <button type="button" class="btn btn-outline-secondary btn-sm" onClick="setRenewal(120)">120</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onClick="setRenewal(130)">130</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onClick="setRenewal(400)">400</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onClick="setFollowUpNav(400, 200)">400 + 200</button>
              @endif
            </div>
          @endforeach
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Enregistrer</button>
    @endif
  </form>

  <script type="text/javascript">
    const lineAmount = {{ $line->getAmount() }};
    const checkCount = {{ $check_count ?? 0 }};

    function toCurrency(amount) {
      return amount.toFixed(2).toString().replace('.', ',') + ' €';
    }
    function setRenewal(amount) {
      const fees = lineAmount - amount;
      if (fees > 0) {
        $('.breakdown-input-PlaneRenewal input').val(amount + ',00 €');
        $('.breakdown-input-CustomerFees input').val(toCurrency(fees));
      } else {
        $('.breakdown-input-PlaneRenewal input').val(toCurrency(lineAmount));
        $('.breakdown-input-CustomerFees input').val('0,00 €');
      }
      validateForm();
    }
    function setFollowUpNav(amount, followUpNav) {
      $('#breakdown_FollowUpNav').prop('checked', true);
      $('.breakdown-input-FollowUpNav').show();
      $('.breakdown-input-PlaneRenewal input').val(amount + ',00 €');
      $('.breakdown-input-FollowUpNav input').val(followUpNav + ',00 €');
      validateForm();
    }
    $(document).ready(function() {
      $('.breakdown_toggle').change(function() {
        var value = $(this).val();
        if ($(this).is(':checked')) {
          $('.breakdown-input-' + value).show();
          if (getTotal() === 0 || getTotal() === '0.00') {
            $('.breakdown-input-' + value + ' input').val(toCurrency(lineAmount));
          }
        } else {
          $('.breakdown-input-' + value).hide();
          $('.breakdown-input-' + value + ' input').val('0,00 €');
        }
      });
      function getTotal() {
        let total = 0;
        $('.breakdown-input').each(function() {
          let value = $(this).val();
          value = value.replace(/[^-0-9,]/g, '');
          value = value.replace(',', '.');
          value = parseFloat(value);
          total += value;
        });
        return total.toFixed(2);
      }
      function validateForm() {
        const checkDelivery = $('select[name="check_delivery"]');
        if (checkDelivery.length > 0) {
          if (checkDelivery.val() === null) {
            alert('Veuillez choisir une remise');
            return false;
          }
          const option = checkDelivery.find(':selected');
          if (lineAmount != option.data('amount')) {
            if (!confirm('Le montant de la ligne ne correspond pas à la somme des chèques')) {
              return false;
            }
          }
          if (checkCount != option.data('count')) {
            if (!confirm('Le nombre de chèques ne correspond pas à la somme des chèques')) {
              return false;
            }
          }
          return true;
        } else {
          if (getTotal() != lineAmount) {
            alert('La somme des ventilations doit être égale au montant de la ligne');
            return false;
          }
        }
        $('button[type="submit"]').prop('disabled', false);
        return true;
      }
      $('.breakdown-input').change(function() {
        validateForm();
      });
      $('form').on("submit", function(event) {
        if (!validateForm()) {
          event.preventDefault();
        }
      });
    });
  </script>
@endsection

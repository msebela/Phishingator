<hr>

<form method="post" action="/portal/<?= $urlSection ?>">
  <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-12">
        <?php $input = 'recieve-email'; ?>
        <div class="form-group mb-4">
          <div class="custom-control custom-checkbox my-1 mr-sm-2">
            <input type="checkbox" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="custom-control-input"<?= (($inputsValues[$input]) ? ' checked' : ''); ?>>
            <label for="<?= $formPrefix . $input ?>" class="custom-control-label h5 font-weight-bold">Ano, chci se dobrovolně přihlásit k&nbsp;odebírání cvičných phishingových zpráv</label>
            <small class="form-text text-muted">
              To znamená, že několikrát do roka do mé e-mailové schránky dorazí e-mail, který bude obsahovat typické znaky phishingu a&nbsp;sociálního inženýrství. Na rozdíl od toho reálného mi ovšem ten cvičný nic neprovede ani neukradne, ale upozorní mě na hrozbu phishingu a&nbsp;na aktuální triky útočníků.
            </small>
          </div>
        </div>

        <?php $input = 'email-limit-checkbox'; ?>
        <div class="form-group mb-4">
          <div class="custom-control custom-checkbox my-1 mr-sm-2">
            <input type="checkbox" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="custom-control-input btn-toggle-display"<?= (($inputsValues[$input] || $_inputsValues['email-limit'] !== NULL) ? ' checked' : ''); ?> data-toggle="#email-limit-count-wrapper">
            <label for="<?= $formPrefix . $input ?>" class="custom-control-label">Omezit počet zpráv, které mi budou zaslány (nepovinné)</label>
          </div>
        </div>

        <?php $input = 'email-limit'; ?>
        <div class="form-group mb-5<?php if (!($inputsValues['email-limit-checkbox'] || $_inputsValues[$input] !== NULL)): ?> d-none<?php endif; ?>" id="email-limit-count-wrapper">
          <label for="<?= $formPrefix . $input ?>">Zbývající počet cvičných phishingových zpráv, o&nbsp;které mám zájem</label>
          <input type="number" name="<?= $formPrefix . $input ?>" id="<?= $formPrefix . $input ?>" class="form-control" min="0" max="1000" value="<?= $inputsValues[$input] ?>">
          <small class="form-text text-muted">Po odeslání každé zprávy dojde ke snížení tohoto čísla. Po dosažení nuly nebude zaslána žádná další zpráva, dokud toto číslo opět nezvýšíte.</small>
        </div>

        <div class="form-group text-center">
          <button type="submit" name="<?= $formPrefix ?>" class="btn btn-primary btn-lg">
            <span data-feather="save"></span>
            Změnit mé nastavení
          </button>
        </div>
      </div>
    </div>
  </div>
</form>
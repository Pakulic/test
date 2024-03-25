// récupération des variables
const DOMAIN = document.querySelector("#sim-recharge-form input.domain");
var rechargeSimSelect = document.querySelector("#sim-recharge-form #sim-recharge-select");
var rechargeSimBtn = document.querySelector("#sim-recharge-form button");
createFormRedirection(rechargeSimSelect, rechargeSimBtn);

// redirection vers la page de l'option sélectionnée
function createFormRedirection(select, btn) {

  if (select && btn) {
    btn.addEventListener("click", () => redirect(select.value));

    function redirect($value) {
      if ($value) {
        $url = DOMAIN.value.concat("", $value);
        return window.open($url, "_blank");
      }
    }
  }
}

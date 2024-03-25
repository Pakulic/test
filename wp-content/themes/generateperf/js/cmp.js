if("querySelector" in document && "addEventListener" in window){
document.addEventListener("DOMContentLoaded", function() {

var loadCmpScripts = function(){
    [].forEach.call(document.querySelectorAll("[data-behind-cmp]"), function(el) {
        el.src = el.dataset.behindCmp;
        el.async = true;
        el.fetchPriority = 'low';
        el.removeAttribute("data-behind-cmp");
    });
}

var toggle_cmp_toggler = function() {
    if(window.display_cmp_toggler === true){
        var cmp = document.createElement('div');
        cmp.classList.add("cmp-toggler");
        cmp.innerHTML = 'Cookies';
        document.body.appendChild(cmp);
        document.body.querySelectorAll(".cmp-toggler")[0].addEventListener("click", function(e) {
            display_cmp_modal();
        }, {passive: true});
    }
}

var display_cmp_modal = function(){
    var cmp = document.createElement('div');
    cmp.classList.add("cmp-wrapper");

    if(window.privacy_policy_url !== ""){
        var display_privacy_url = '<p><small><a href="'+window.privacy_policy_url+'" target="_blank">Politique de confidentialité</a></small></p>';
    } else {
        var display_privacy_url = "";
    }

    cmp.innerHTML = '<div class="cmp"><h3>Gestion des cookies</h3><p>Nous utilisons des cookies et d’autres technologies de suivi pour améliorer votre expérience de navigation sur notre site, pour vous montrer un contenu personnalisé et des publicités ciblées, pour analyser le trafic de notre site et pour comprendre la provenance de nos visiteurs.</p><p class="text-center"><button data-cmp-valid="false">Refuser</button> <button data-cmp-valid="true">Accepter et poursuivre &rarr;</button></p>'+display_privacy_url+'</div>';
    
    var cmp_toggler = document.body.querySelectorAll(".cmp-toggler");
    if(cmp_toggler.length){
        cmp_toggler[0].remove();
    }
    
    document.body.appendChild(cmp);
    document.body.classList.toggle("overlayed");

    [].forEach.call(document.body.querySelectorAll("[data-cmp-valid]"), function(el) {
        el.addEventListener("click", function(e) {
            localStorage.setItem('cmp-consent', this.dataset.cmpValid);
            document.body.querySelectorAll(".cmp-wrapper")[0].remove();
            document.body.classList.toggle("overlayed");
            if(this.dataset.cmpValid === "true"){
                loadCmpScripts();
            }
            toggle_cmp_toggler();
        }, {passive: true});
      });
}

/* CMP acceptée ? On charge les scripts */
if(localStorage.getItem('cmp-consent')){
    if(localStorage.getItem('cmp-consent') === "true"){
        loadCmpScripts();
    }
    toggle_cmp_toggler();
/* CMP non validée ? On Affiche la popup */
} else if(localStorage.getItem('cmp-consent') === null) {
    display_cmp_modal();
}

});
}
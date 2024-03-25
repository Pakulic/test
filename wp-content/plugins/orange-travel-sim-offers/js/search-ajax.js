(function ($) {

    function ajax_countries_search(ajaxurl, data) {
        $( "#results" ).html('');
        $("#countryLinkBtn").val( '');
        // Requête Ajax en JS natif via Fetch
        fetch(ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "Cache-Control": "no-cache",
            },
            body: new URLSearchParams(data),
        })
            .then((response) => response.json())
            .then((body) => {
                // En cas d'erreur
                if (!body.success) {
                    alert(response.data);
                    $("#selectChild").addClass("none");
                    return;
                }

                    body.data.forEach(function( item ) {
                        let resultBtn=  $('<button></button>', {
                            class: 'resultBTn',
                            value : item.link,
                        });

                        if(item.flag && item.flagURL){
                            let resultDiv=  $("<div></div>", {
                                class : 'flag ' + item.flag,
                                style : 'background-image: url(' + item.flagURL + ')'
                            });
                            resultBtn.append(resultDiv);
                        }

                        let resultP=  $("<p></p>").text(item.title);
                        resultBtn.append(resultP);

                        $( "#results" ).append(resultBtn);

                        resultBtn.each(function( ) {
                            $(this).on( 'click', function(e) {
                                e.preventDefault();
                                $( "#selectChild" ).addClass("none");
                                $("#countrieslist").val( $(this).text() );
                                $("#countryLinkBtn").val( $(this).val() );
                            });
                        });

                    });

            }) .catch((error) =>  {
                console.log(error) ;
                $("#selectChild").addClass("none");
        })
        return false;
    }

  $(document).ready(function () {
      // comportement du champs de recherche par pays
      let searchForm =   $( "#searchform");
      searchForm.on( "keyup", function(e) {
            e.preventDefault();
            let ajaxurl = $(this).attr("action");
            let data = {
                action: $(this).find("input[name=action]").val(),
                nonce: $(this).find("input[name=nonce]").val(),
                keyword: $("#countrieslist").val().trim()
            };
          $("#selectChild").removeClass("none");
          ajax_countries_search(ajaxurl, data);
    });

          searchForm.on('click', function(e) {
              e.preventDefault();
              if($("#countrieslist").val(" ")) {
                  let ajaxurl = $(this).attr("action");

                  let data = {
                      action: $(this).find("input[name=action]").val(),
                      nonce: $(this).find("input[name=nonce]").val(),
                      keyword: "",
                  };
                  $("#selectChild").removeClass("none");
                  ajax_countries_search(ajaxurl, data);
              }
          });



      // empecher le comportement "enter"
      searchForm.on( "submit", function(e) {
          e.preventDefault();
          return false;
      });
      // comportement du bouton permattant d'afficher le menu déoulant des paus
      $( "#selectBtn" ).click( function(e) {
          e.preventDefault();
          let ajaxurl = $(this).data('ajaxurl');

          let data = {
              action: $(this).data('action'),
              nonce:  $(this).data('nonce')
          }
          $("#selectChild").toggleClass("none");
          ajax_countries_search(ajaxurl, data);
      });

      // résultat en cas de clic sur le bouton "selectionner" => renvoi vers la page de l'option sélectionnée
      $( "#countryLinkBtn" ).click( function(e) {
          e.preventDefault();
          if( $(this).val() ){
              let url = $( "#sim-purchase-form input.domain" ).val().concat("", $(this).val() );
              return window.open(url, "_blank");
          }

      });


  });

})(jQuery);

(function(window){
    "use strict";

    // Ready
    document.addEventListener("DOMContentLoaded", function(){
        "use strict";

        // Change Database Type
        var dbSelect = document.getElementById("db-type"),
            dbChange = function(value){
                var fields = document.querySelectorAll("[data-db]");

                for(var i = 0, t; i < fields.length; i++){
                    t = fields[i].getAttribute("data-db").split("|");
                    if(t.indexOf(value) > -1){
                        fields[i].removeAttribute("style");
                    } else {
                        fields[i].style.display = "none";
                    }
                }

                // Change default Port
                if(value == "mysql"){
                    document.getElementById("db-port").value = "3306";
                } else if(value == "pgsql"){
                    document.getElementById("db-port").value = "5432";
                }
            };
        if(dbSelect){
            dbSelect.addEventListener("change", function(){
                dbChange(dbSelect.value);
            });
            dbChange(dbSelect.value);
        }
    });
})(this);

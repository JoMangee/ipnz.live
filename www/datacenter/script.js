function eob_select(e) {
    const eob = document.getElementById("existingorback").value;
    document.getElementById("existingorback").value = e.target.value;
    const sios = document.getElementById("selectifoutsource");
    const ios = document.getElementById("ifoutsource");
    // alert(eob);
    
    const x = document.getElementById("formoutsource");
    if (eob == 0) {
        x.style.display = "block";
        sios.required = true;
    } else {
        x.style.display = "none";
        sios.removeAttribute("required");
        
    }
    sios.selectedIndex = 0;
    ios.value = "";
}

function ios_select(e){
    document.getElementById("ifoutsource").value = e.target.value;
}
function abrirVentana(url, Nombre, Alto, Ancho) {
    windowObjectReference2 = window.open(url, Nombre, "toolbar=0, width=" + Ancho + ", height=" + Alto + ",location=0,status=1,menubar=0,scrollbars=1,resizable=0");
    windowObjectReference2.focus();
}


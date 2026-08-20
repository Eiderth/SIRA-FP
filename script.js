import Enrutador_global from "./frontend/controllers/index.js";

document.addEventListener('DOMContentLoaded',() => {
    const enrutador = new Enrutador_global();

    enrutador.init();
});
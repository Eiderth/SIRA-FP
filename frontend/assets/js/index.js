import neblina from "./animaciones/neblina.js";

const observador = new MutationObserver((mutaciones) => {
    mutaciones.forEach((mutacion) => {
        mutacion.addedNodes.forEach((nodo)=>{
            if (nodo.nodeType === Node.ELEMENT_NODE) {
                if(nodo.classList.contains('neblina')){
                    crear_neblina(nodo)
                }

                const noditos = document.querySelectorAll('neblina');
                noditos.forEach(sub => crear_neblina(sub))
            }
        })
    })
})

observador.observe(document.getElementById('root'), {childList: true, subtree: true});

const crear_neblina = (nodo) => {

    const id_intervalo = setInterval(()=>{
        if(!document.body.contains(nodo)){
            clearInterval(id_intervalo);
            return;
        }
        neblina(nodo)
    },500)
}
export default class Utils {

    #notificacion;
    
    async _notificacion(text) {
        if (!this.#notificacion) this.#notificacion = document.getElementById('notificacion');
        this.#notificacion.textContent = text;
        this.#notificacion.style.transform = 'translate(-50%, 0%)'
        setTimeout(()=> {
            this.#notificacion.style.transform = 'translate(-50%, -200%)'
        }, 2000)
    }

    async _enviar_datos(url, objeto){
        try {
            const json = await fetch(url, {
            method: 'POST',
            body: JSON.stringify(objeto), 
            headers: { 'Content-type' : 'application/json'} 
            })
            if(!json.ok) throw new Error(`Error ${json.status}: ${json.statusText}`);
            const data = await json.json();
            if (data.estado !== 'completado') throw new Error(data.mensaje || 'Error desconocido', {cause: data.Error});
            return data;
        } catch(e) {
            console.error('Mensaje: ', e.message);
            if(e.cause) console.error('Causa: ', e.cause);
            return {'estado': 'error', 'mensaje': e.message};
        }
    }

    async _traer_datos (ruta) {
        try {
            const json = await fetch(ruta);
            if (!json.ok) throw new Error(`Error ${json.status}: ${json.statusText}`);
            const data = await json.json()
            if(data.estado != 'completado') throw new Error(data.mensaje || 'Error desconocido', {cause: data.Error});
            return data;
        }catch(e) {
            console.error('Mensaje: ', e.message);
            if(e.cause) console.error('Causa: ', e.cause);
            return {'estado': 'error', 'mensaje': e.message};
        } 
    }

    async _traer_html(ruta){
        try {
            const resp = await fetch(ruta);
            if (!resp.ok) throw new Error(`Error ${resp.status}: ${resp.statusText}`);
            const html = await resp.text();
            return html;
        } catch(e) {
            console.log(e);
            return '<span class="text-danger">Error al traer el html</span>'
        }
    }

    async _inyectar_html(contenedor, html) {
        contenedor.classList.remove('show')
        await new Promise((resolve) => {
            setTimeout(() => {
                contenedor.innerHTML = html;
                contenedor.classList.add('show');
                resolve();
            },150)
        })
    }

    _llenar_select(padre, array, valor_defecto, propiedad = 'id') {
        padre.innerHTML = ''
        array.forEach((a) => {
            const option = document.createElement('option');
            option.textContent = a.nombre;
            option.value = a[propiedad];
            if (a.nombre == valor_defecto) {option.selected = true}
            padre.appendChild(option)
        })
    }

    _llenar_inputs(data) {
       for (const [clave, valor] of Object.entries(data)) {
            const radio = document.querySelector(`[name="${clave}"][value="${valor}"]`);
            if (radio) {
                radio.checked = true
                continue;
            }
            const input = document.querySelector(`[name="${clave}"]`);
            if (input) input.value = valor;
       }
    }

    _validar_formulario(nodo_forms) {
        for (const form of nodo_forms) {
            if(!form.reportValidity()){
                return form.reportValidity();
            }
        }
        return true;
    }

    _limpiar_objeto(objeto) {
        for (let clave in objeto) {
            if (objeto[clave] == '' || objeto[clave] == null) {
                objeto[clave] = null;
            } else if(typeof objeto[clave] === 'string') {
                objeto[clave] = objeto[clave]
                    .trim()
                    .split(/\s+/) 
                    .map(cad => cad.charAt(0).toUpperCase() + cad.slice(1).toLowerCase())
                    .join(' ');
            }
        }
    }


}
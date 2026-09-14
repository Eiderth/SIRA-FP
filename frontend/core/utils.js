export default class Utils {

    static ERRORES = {
        'error_en_db': 'ha ocurrido un error en la base de datos',
        'error_al_iniciar_sesion': 'No se pudo iniciar la sesión.',
        'error_al_cerrar_session': 'No se pudo cerrar la sesión correctamente.',
        'datos_invalidos' : 'Algo salio mal verifique los datos ingresados',
        'campos_incompletos': 'Por favor llena todos los campos.',
        'credenciales_invalidas': 'Usuario o contraseña incorrectos.',
        'usuario_ya_registrado': 'El usuario ya se encuentra registrado.',
        'no_autorizado': 'Usted no esta autorizado a esta accion',
        'session_expirada': 'Tu sesión ha expirado.',
        'error_de_servidor': 'Ocurrió un error en el servidor.',
        'accion_no_encontrada' : 'Parece que esta accion esta en desarrollo',
        'error_desconocido': 'Parece que ha ocurrido un error imprevisto.',

        'error_al_crear_usuario' : 'Parece que ha ocurrido un error al crear',
        'error_al_eliminar_usuario' : 'Parece que ha ocurrido un error al eliminar',
        'usuario_no_encontrado' : 'No se encontro en el sistema, verifique sus datos',

    };

    #notificacion;
    #notificacion_ocupada = false;

    constructor() {
        if (new.target === Utils) {
            throw new Error("La clase 'Utils' no se puede instanciar directamente. Debe ser heredada.");
        }
    }
    
    async _notificacion(text, titulo = 'Mensaje', time = 3000) {
        if (this.#notificacion_ocupada) return;
        if (!this.#notificacion) this.#notificacion = document.getElementById('notificacion');

        this.#notificacion_ocupada = true;
        this.#notificacion.innerHTML = `
            <div class="d-flex flex-column text-start">
                <span class="fw-bold ${titulo == 'Error'? 'text-danger' : 'text-success'} fs-6">${titulo}</span>
                <span class="text-dark small">${text}</span>
            </div>
        `;

        this.#notificacion.style.transform = 'translate(-50%, 0%)';
        setTimeout(() => {
            this.#notificacion.style.transform = 'translate(-50%, -200%)';
            this.#notificacion_ocupada = false;
        }, time);
    }

    _manejar_sesion_expirada() {
        setTimeout(() => {
            window.dispatchEvent(new CustomEvent('sesion:expirada'));
        }, 0);
    }

    async _enviar_datos(url, objeto){
        try {
            const json = await fetch(url, {
                method: 'POST',
                body: JSON.stringify(objeto), 
                headers: { 'Content-type' : 'application/json'} 
            });
            const data = await json.json();

            if (json.status === 401 && data.error === 'session_expirada') {
                this._manejar_sesion_expirada();
                return;
            } 

            if (!json.ok || data.estado !== 'completado') {   
                const claveError = data.error || 'error_desconocido';
                const mensaje_error = Utils.ERRORES[claveError] || Utils.ERRORES['error_desconocido'];

                throw new Error( mensaje_error , { cause: data.error });
            }

            delete data.estado;
            return data;

        } catch(e) {
            // const mensaje_error = (e instanceof TypeError) ? 'Sin conexión con el servidor.' : e.message;
            this._notificacion(e.message, 'Error');
            console.error(`Mensaje: ${e.message} ${e.cause? `Causa: ${e.cause}`: ''}`);
            return {};        
        }
    }

    async _traer_datos (ruta) {
        try {
            const json = await fetch(ruta);
            const data = await json.json();

            if (json.status === 401 && data.error === 'session_expirada') {
                this._manejar_sesion_expirada();
                return;
            } 

            if (!json.ok || data.estado !== 'completado') {   
                const claveError = data.error || 'error_desconocido';
                const mensaje_error = Utils.ERRORES[claveError] || Utils.ERRORES['error_desconocido'];
            
                throw new Error( mensaje_error , { cause: data.error });
            }

            delete data.estado;
            return data;

        }catch(e) {
            // const mensaje_error = (e instanceof TypeError) ? 'Sin conexión con el servidor.' : e.message;
            this._notificacion(e.message, 'Error');
            console.error(`Mensaje: ${e.message} ${e.cause? `Causa: ${e.cause}`: ''}`);
            return {};         
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
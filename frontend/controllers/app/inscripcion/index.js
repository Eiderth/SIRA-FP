import Utils from "../../../core/utils.js";
import Seccion_1_controller from "./secciones/seccion_1.js";
import Seccion_2_controller from "./secciones/seccion_2.js";
import Seccion_3_controller from "./secciones/seccion_3.js";
import Seccion_4_controller from "./secciones/seccion_4.js";
import Seccion_exito from "./interfaz-final/seccion_exito.js";
import Seccion_error from "./interfaz-final/seccion_error.js";

export default class Inscripcion_controller extends Utils {

   _data = {
      inscripcion: {},
      estudiante: {
         persona: {},
         persona_estudiante: {},
         antropometrico: {},
         salud: {},
         extra_curricular: {},
      },
      representante_principal: {
         persona: {},
         persona_representante: {},
         direccion: {},
      },
      representante_secundario: {
         persona: {},
         persona_representante: {},
         direccion: {},
      },
   }

   #parametros_formulario; 

   #secciones_controllers = [];
   #seccion_exito;
   #seccion_error;

   #cont = 1; 
   #html;
   #btn_siguiente;
   #btn_atras;
   #btn_reset; 

   constructor() {
      super(); 
      this.#html = this._traer_html('./frontend/views/app/inscripcion/index.html');
   }

   async init() {
      const html = await this.#html;
      
      await this._inyectar_html(document.getElementById('root-app'), html);
      
      if (this.#cont === 1) {

         this.#parametros_formulario = await this.#traer_parametros();
         
         this.#secciones_controllers = [
            new Seccion_1_controller(this.#parametros_formulario, (data) => this._alterar_data(data)),
            new Seccion_2_controller(),
            new Seccion_3_controller(this.#parametros_formulario, (data) => this._alterar_data(data)),
            new Seccion_4_controller(this.#parametros_formulario, (data) => this._alterar_data(data))
         ];

         this.#seccion_exito = new Seccion_exito();
         this.#seccion_error = new Seccion_error();

      }

      this.#secciones_controllers[this.#cont -1]?.init(this.#obtener_data_por_cont(this.#cont));
      
      this.#btn_siguiente = document.getElementById('btn-siguiente');
      this.#btn_atras = document.getElementById('btn-atras');
      this.#btn_reset = document.getElementById('btn-reset');

      this.#btn_siguiente.addEventListener('click', () => this.#siguiente());
      this.#btn_reset.addEventListener('click', () => this.#reset());
      this.#btn_atras.addEventListener('click', () => this.#atras());

   }

   async #traer_parametros() {
      try {
         let geografia = localStorage.getItem('geografia_formulario');
         let datos_geografia = geografia ? JSON.parse(geografia) : null;

         if (!datos_geografia) {
            const respuesta_geo = await this._traer_datos('./api.php?controller=inscripcion_controller&action=obtener_geografia');
            if (respuesta_geo) {
               datos_geografia = respuesta_geo;
               localStorage.setItem('geografia_formulario', JSON.stringify(datos_geografia));
            }
         }
         
         const parametros_acad = await this._traer_datos('./api.php?controller=inscripcion_controller&action=obtener_parametros_academicos');

         return {
            periodos: parametros_acad?.periodos || [],
            secciones: parametros_acad?.secciones || [],
            grados: parametros_acad?.grados || [],
            niveles_academicos: parametros_acad?.niveles_academicos || [],
            grados_secciones: parametros_acad?.grados_secciones || [],
            paises: datos_geografia?.paises || [],
            estados: datos_geografia?.estados || [],
            municipios: datos_geografia?.municipios || [],
            parroquias: datos_geografia?.parroquias || []
         };

      } catch (error) {
         console.error("Error al cargar los parámetros del formulario:", error);
         this._notificacion('Error al sincronizar parámetros', 'Error');
         return null;
      }
   }


   async #siguiente() {
      if (!this._validar_formulario(document.querySelectorAll('form'))) return;
      
      if (this.#cont == 1) {
         this._data.inscripcion = Object.fromEntries(new FormData(document.getElementById('formulario-inscripcion')));
         this._data.estudiante.persona = Object.fromEntries(new FormData(document.getElementById('formulario-persona')));
         this._data.estudiante.persona_estudiante = Object.fromEntries(new FormData(document.getElementById('formulario-persona-estudiante')));
         
         const grado_encontrado = this.#parametros_formulario.grados_secciones.find(
            gs => gs.grado_id == this._data.inscripcion.grado_id && gs.seccion_id == this._data.inscripcion.seccion_id
         );

         this._data.inscripcion.grado_seccion_id = grado_encontrado ? grado_encontrado.id : null;

         this.#btn_atras.classList.remove('d-none'); 
      } 

      if (this.#cont == 2) {
         this._data.estudiante.antropometrico = Object.fromEntries(new FormData(document.getElementById('formulario-antropometrico')));
         this._data.estudiante.salud = Object.fromEntries(new FormData(document.getElementById('formulario-salud')));
         this._data.estudiante.extra_curricular = Object.fromEntries(new FormData(document.getElementById('formulario-extra-curricular')));
      } 

      if (this.#cont == 3) {
         this._data.representante_principal.persona = Object.fromEntries(new FormData(document.getElementById('formulario-persona')));
         this._data.representante_principal.persona_representante = Object.fromEntries(new FormData(document.getElementById('formulario-persona-representante')));         
         this._data.representante_principal.direccion = Object.fromEntries(new FormData(document.getElementById('formulario-direccion')));
      }

      if (this.#cont == 4) {
         this._data.representante_secundario.persona = Object.fromEntries(new FormData(document.getElementById('formulario-persona')));
         this._data.representante_secundario.persona_representante = Object.fromEntries(new FormData(document.getElementById('formulario-persona-representante')));         
         this._data.representante_secundario.direccion = Object.fromEntries(new FormData(document.getElementById('formulario-direccion')));
      }

      this.#cont++;

      if (this.#cont != 5) {
         await this.#secciones_controllers[this.#cont -1].init();
         this._llenar_inputs(this.#obtener_data_por_cont(this.#cont));
         if (this.#cont == 4) this.#btn_siguiente.textContent = 'Inscribir';
         
      } else {

         delete this._data.estudiante.persona_estudiante.estado_nacimiento_id;
         delete this._data.estudiante.persona_estudiante.municipio_nacimiento_id;
         
         delete this._data.inscripcion.nivel_academico_id;
         delete this._data.inscripcion.grado_id;
         delete this._data.inscripcion.seccion_id;

         delete this._data.representante_principal.direccion.estado_id;
         delete this._data.representante_secundario.direccion.estado_id;
         delete this._data.representante_principal.direccion.municipio_id;
         delete this._data.representante_secundario.direccion.municipio_id;

         // === Limpieza ===
         this._limpiar_objeto(this._data.inscripcion);
         
         for (let clave in this._data.estudiante) { 
            this._limpiar_objeto(this._data.estudiante[clave]);
         }

         for (let clave in this._data.representante_principal) { 
            this._limpiar_objeto(this._data.representante_principal[clave]);
         }

         for (let clave in this._data.representante_secundario) { 
            this._limpiar_objeto(this._data.representante_secundario[clave]);
         }

         const { llave_inscripcion = null, cedula_escolar = null, mensaje = null } = await this._enviar_datos('./api.php?controller=inscripcion_controller&action=guardar_estudiante', this._data);

         if (llave_inscripcion && cedula_escolar) {
            
            await this.#seccion_exito.init(
               this._data.estudiante, 
               llave_inscripcion, 
               cedula_escolar,
            );

            setTimeout(() => {
               this.#cont = 1;
               this.#reset(document.getElementById('secciones-root') ? true: false);
               this.#btn_siguiente.textContent = 'Siguiente';
            }, 4000);

         } else {
            this.#cont = 4;


            this.#seccion_error.init(mensaje, () => {
               this.#secciones_controllers[this.#cont -1].init(
                  this.#obtener_data_por_cont(4)
               );
            });

         }
      } 
   }

   #reset(init = true) {
      if (this.#cont == 1) {   
         Object.assign(this._data, {
            inscripcion: {},
            estudiante: {
               persona: {},
               persona_estudiante: {},
               antropometrico: {},
               salud: {},
               extra_curricular: {},
            },
            representante_principal: {
               persona: {},
               persona_representante: {},
               direccion: {},
            },
            representante_secundario: {
               persona: {},
               persona_representante: {},
               direccion: {},
            },
         });
      }

      if (this.#cont === 2) {   
         this._data.antropometrico = {}
         this._data.salud = {}   
         this._data.extra_curricular = {}   
      }

      if (this.#cont === 3) {
         Object.assign(
            this._data.representante_principal,
            {
               persona: {},
               persona_representante: {},
               direccion: {}
            }   
         )
      }
      
      if (this.#cont === 4) {
         Object.assign(
            this._data.representante_secundario,
            {
               persona: {},
               persona_representante: {},
               direccion: {}
            }   
         )
      }

      document.querySelectorAll('form')?.forEach(form => form.reset());
      
      if(init) this.#secciones_controllers[this.#cont -1].init();

      if (this.#cont == 4) this.#btn_siguiente.textContent = 'Inscribir';
   }

   async #atras() {
      if (this.#cont == 2) {
         this._data.estudiante.antropometrico = Object.fromEntries(new FormData(document.getElementById('formulario-antropometrico')));
         this._data.estudiante.salud = Object.fromEntries(new FormData(document.getElementById('formulario-salud')));
         this._data.estudiante.extra_curricular = Object.fromEntries(new FormData(document.getElementById('formulario-extra-curricular')));
      } 

      if (this.#cont == 3) {
         this._data.representante_principal.persona = Object.fromEntries(new FormData(document.getElementById('formulario-persona')));
         this._data.representante_principal.persona_representante = Object.fromEntries(new FormData(document.getElementById('formulario-persona-representante')));
         this._data.representante_principal.direccion = Object.fromEntries(new FormData(document.getElementById('formulario-direccion')));
      }

      if (this.#cont == 4) {
         this._data.representante_secundario.persona = Object.fromEntries(new FormData(document.getElementById('formulario-persona')));
         this._data.representante_secundario.persona_representante = Object.fromEntries(new FormData(document.getElementById('formulario-persona-representante')));
         this._data.representante_secundario.direccion = Object.fromEntries(new FormData(document.getElementById('formulario-direccion')));
      }

      this.#cont--;
      await this.#secciones_controllers[this.#cont -1].init();
      this._llenar_inputs(this.#obtener_data_por_cont(this.#cont));
      
      if (this.#cont === 1) this.#btn_atras.classList.add('d-none');

      if (this.#cont == 3) this.#btn_siguiente.textContent = 'Siguiente';
      
      if (this.#cont == 4) this.#btn_siguiente.textContent = 'Inscribir';
      
   }

   _alterar_data(data) {   
      Object.assign(this._data, data);      
   }

   #obtener_data_por_cont(cont) {
      if (cont === 4) {
         return {
            ...this._data.representante_secundario.persona,
            ...this._data.representante_secundario.persona_representante,
            ...this._data.representante_secundario.direccion
         }

      } else if (cont === 3) {
         return {
            ...this._data.representante_principal.persona,
            ...this._data.representante_principal.persona_representante,
            ...this._data.representante_principal.direccion
         }
      } else if (cont === 2) {
         return {
            ...this._data.estudiante.antropometrico,
            ...this._data.estudiante.salud,
            ...this._data.estudiante.extra_curricular
         }
      } else {
         return {
            ...this._data.estudiante.persona,
            ...this._data.estudiante.persona_estudiante,
            ...this._data.inscripcion
         }
      }
   }
}
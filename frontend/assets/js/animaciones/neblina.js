const neblina = (etiqueta) => {
    const div = document.createElement('div');

    div.classList.add('particula');

    div.style.left = `${Math.random() * 100}%`;

    const tamaño = Math.random() * 140 + 60;

    div.style.width = `${tamaño}px`;
    div.style.height = `${tamaño}px`;

    const duracion = Math.random() * 3 + 2;
    div.style.animationDuration = `${duracion}s`;

    etiqueta.appendChild(div);

    setTimeout(()=>{div.remove() }, duracion * 1000);
}

export default neblina;
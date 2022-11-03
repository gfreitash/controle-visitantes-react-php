import React, {useEffect} from "react";

export default function Titulo(props) {
    const titulo = {
        display: "flex",
        justifyContent: "space-between",
        alignItems: "center"
    }

    useEffect(() => {
        let titulo = props.titulo ? props.titulo : props.children;
        document.title = `Controle de Visitantes - ${titulo}`;
    },[props.children]);

    return (
        <>
            <div style={titulo}>
                <h2 id="titulo-heading">{props.children}</h2>
                {props.conteudoTitulo}
            </div>
        </>
    )
}

import React from "react";

export default function Titulo(props) {
    const titulo = {
        display: "flex",
        justifyContent: "space-between",
        alignItems: "center"
    }

    return (
        <>
            <div style={titulo}>
                <h2 id="titulo-heading">{props.children}</h2>
                {props.conteudoTitulo}
            </div>
        </>
    )
}
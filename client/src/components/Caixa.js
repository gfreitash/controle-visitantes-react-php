import React from "react";

import "../assets/css/caixa.css"
import {Link} from "react-router-dom";

export default function Caixa({conteudo, contador, razao, width, color, link}) {
    let style = {}
    width && (style.width = width);
    color && (style.backgroundColor = color);

    let ratio;
    switch (razao) {
        case "2:1":
            ratio = "caixa__razao--2_1";
            break;
        case "1:2":
            ratio = "caixa__razao--1_2";
            break;
        case "1:1":
            ratio = "";
            break;
        default:
            ratio = "";
    }

    const caixa = (
        <div className="caixa__conteudo d-flex flex-column align-items-center justify-content-center">
            {conteudo ?? "Conteúdo"}
            <div className="caixa__contador">
                {contador ?? 0}
            </div>
        </div>
    );

    return (
        <div className={`caixa ${ratio}`} style={style}>
            {link ? (<Link to={link}> {caixa} </Link>) : caixa}
        </div>
    )
}

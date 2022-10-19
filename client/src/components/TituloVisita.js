import React, {useEffect} from "react";

import "../assets/css/titulo-visita.css";

import {
    faDoorOpen,
    faDoorClosed,
    faSpinner,
    faPersonWalkingDashedLineArrowRight,
    faPenToSquare,
} from "@fortawesome/free-solid-svg-icons";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";

import Titulo from "./Titulo";

export default function TituloVisita(props) {
    const [icone, setIcone] = React.useState(faSpinner);
    const [status, setStatus] = React.useState("");
    const [statusLabel, setStatusLabel] = React.useState("");

    useEffect(() => {
        switch (props.status) {
            case "aberta":
                setIcone(faDoorOpen);
                setStatus("aberta");
                setStatusLabel("Em aberto");
                break;
            case "fechada":
                setIcone(faDoorClosed);
                setStatus("fechada");
                setStatusLabel("Fechada");
                break;
            default:
                setIcone(faSpinner);
                setStatus("");
                setStatusLabel("");
        }
    }, [props]);

    const conteudoTitulo = status === "aberta" && (
        <div>
            <FontAwesomeIcon icon={faPenToSquare} className="fa-2xl me-3 interativo" onClick={props.onEditar}/>
            <FontAwesomeIcon icon={faPersonWalkingDashedLineArrowRight} className="fa-2xl interativo" onClick={props.onFinalizar}/>
        </div>
    )

    return (
        <>
            <div id="status-visita" className={`status-visita ${status}`}>
                <div id="status">
                    <FontAwesomeIcon icon={icone} className="me-2"/>
                    <span>{statusLabel}</span>
                </div>

                <Titulo conteudoTitulo={conteudoTitulo}>
                    Visita nº {props.id}
                </Titulo>
            </div>
        </>
    )
}

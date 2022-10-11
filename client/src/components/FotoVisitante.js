import React, {useEffect, useRef, useState} from "react";

import "../assets/css/foto-visitante.css";

import foto_padrao from "../assets/imgs/padrao.jpg";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {faTrashCan} from "@fortawesome/free-solid-svg-icons";


export default function FotoVisitante(props) {
    const inputFotoRef = useRef();
    const fotoClienteRef = useRef();

    const [excluirFoto, setExcluirFoto] = useState(false);
    const [foto, setFoto] = useState(props.foto ? props.foto : foto_padrao);

    function removerImagem(estrito = true) {
        if (props.foto === foto && estrito) {
            setExcluirFoto(true);
        }
        if(estrito) {
            fotoClienteRef.current.src = foto_padrao;
            setFoto(foto_padrao);
        }
        if(inputFotoRef.current) {
            inputFotoRef.current.value = "";
        }
    }

    function paraBase64(arquivo) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(arquivo);
            reader.onload = () => resolve(reader.result);
            reader.onerror = error => reject(error);
        });
    }

    async function handleFotoInput() {
        const inputFoto = inputFotoRef.current;
        const arquivo = inputFoto?.files[0];

        let extensoesPermitidas = /(\.jpg|\.jpeg|\.png|\.webp)$/
        if(!extensoesPermitidas.exec(inputFoto?.value)) {
            alert("Os formatos permitidos de arquivo são apenas .jpg, .jpeg, .png e .webp");
            removerImagem(false);
            return;
        }
        let tamanhoMaximo = 2*1024*1024; // 2MB
        if(arquivo.size > tamanhoMaximo) {
            alert("O arquivo deve ter menos de 2MB");
            removerImagem(false);
            return;
        }
        let base64 = await paraBase64(arquivo);
        setFoto(base64);
    }

    function handleRemoverFoto() {
        removerImagem();
    }

    useEffect(() => {
        setFoto(props.foto ? props.foto : foto_padrao);
    },[props.foto]);

    useEffect(() => {
        setFoto(props.foto ? props.foto : foto_padrao);
        setExcluirFoto(false);
    },[props.editavel]);

    return (
        <div className="form-foto" id="form-foto">
            <div className="foto-preview" id="foto-preview">
                <img alt="Foto do cliente" className="foto-cliente" src={foto}
                     height="190px" width="100%" id="fotoCliente" ref={fotoClienteRef} key={foto}/>
            </div>

            <input type="hidden" name="excluirFoto" id="excluirFoto" value={excluirFoto.toString()} disabled={props.disabled}/>

            {props.editavel && (
                <div className="foto-input">
                    <label htmlFor="fotoInput" className="btn btn-dark btn-sm btn-file">
                        <div id="arquivo">Escolha um arquivo</div>
                        <input readOnly className="form-file" type="file" name="fotoInput" id="fotoInput"
                               accept="image/png, image/jpg, image/jpeg, image/webp"
                               onChange={handleFotoInput} ref={inputFotoRef}/>
                    </label>
                    <div className="btn btn-dark btn-sm btn-remover" id="removerFoto" onClick={handleRemoverFoto}>
                        <FontAwesomeIcon icon={faTrashCan}/>
                    </div>
                </div>
            )}
        </div>
    )
}

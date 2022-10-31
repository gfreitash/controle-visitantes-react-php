import React, {useEffect, useRef, useState} from "react";

import useQuery from "../hooks/useQuery";
import useAuth from "../hooks/useAuth";
import useAxiosPrivate from "../hooks/useAxiosPrivate";

import "../assets/css/form-cadastro.css"
import {validaCPF} from "../assets/js/dados-visitante";

import DadosVisitante from "../components/DadosVisitante";
import FotoVisitante from "../components/FotoVisitante";
import Titulo from "../components/Titulo";
import RegistroVisitante from "../components/RegistroVisitante";
import BotoesAcao from "../components/BotoesAcao";
import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";
import Alerta from "../components/Alerta";

export default function Visitante() {
    const query = useQuery();
    const axios = useAxiosPrivate();
    const {auth} = useAuth();
    const handleInvalidSession = useInvalidSessionHandler();

    const [cpf, setCpf] = useState(query.get("cpf") ?? "");

    const [conteudoTitulo, setConteudoTitulo] = useState((<></>));
    const [editavel, setEditavel] = useState(false);
    const [resultadoBusca, setResultadoBusca] = useState({});
    const [alerta, setAlerta] = useState({tipo: "", mensagem: ""});

    const alertaRef = useRef();
    const formRef = useRef();

    const onCpfValido = (codigoResposta, visitante) => {
        if (codigoResposta === 200) {
            setResultadoBusca(visitante);
        }
    }

    const onClickSalvar = () => {
        formRef.current?.requestSubmit();
        setEditavel(false);
    }

    const handleSubmit = async (event) => {
        event.preventDefault();
        if(!validaCPF(event.target.cpf, ()=>{})) {
            setAlerta({tipo: "danger", mensagem: "CPF não é válido, por favor insira um CPF válido."});
            event.target.cpf.classList.add("is-invalid");
            return;
        }

        setEditavel(false);

        try {
            let formData = new FormData(event.target)
            formData.append("idUsuario", auth.id);
            formData.append("id", resultadoBusca.id);

            const resposta = await axios.put("/visitante", formData, {
                headers: {
                    "Content-Type": "multipart/form-data"
                }
            });
            if(resposta?.status === 200) {
                setAlerta({tipo: "success", mensagem: "Visitante atualizado com sucesso."});
                setResultadoBusca(resposta.data);
                setCpf(event.target.cpf.value);
            }
        } catch (error) {
            switch (error.response.status) {
                case 400:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível atualizar o visitante. Verifique os dados e tente novamente."});
                    break;
                case 401:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível atualizar o visitante. Sua sessão expirou."});
                    handleInvalidSession();
                    break;
                case 409:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível atualizar o visitante. O CPF informado já está cadastrado."});
                    break;
                case 500:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível atualizar o visitante. Ocorreu um erro interno no servidor."});
                    break;
                case 0:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível atualizar o visitante. Não foi possível se conectar ao servidor."});
                    break;
                default:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível atualizar o visitante. Ocorreu um erro desconhecido."});
            }
        }
    }

    useEffect(() => {
        if(resultadoBusca.id) {
            setConteudoTitulo(
                <BotoesAcao
                    cpf = {cpf}
                    onClickEditar={()=>{setEditavel(true)}}
                    onClickCancelar={()=>{setEditavel(false)}}
                    onClickSalvar={onClickSalvar}
                />
            );
        }
    },[resultadoBusca, editavel]);

    useEffect(() => {
        if (editavel) {
            setAlerta({tipo: "", mensagem: ""});
        }
    }, [editavel]);

    return (
        <div className="form-fieldset">
            <Titulo conteudoTitulo={conteudoTitulo}>Visitante</Titulo>
            <hr/>
            <Alerta alerta={alerta} setAlerta={setAlerta} ref={alertaRef}/>
            <form ref={formRef} className="form" encType='multipart/form-data' onSubmit={handleSubmit}>
                <FotoVisitante foto={resultadoBusca.id ? resultadoBusca.foto : ""} editavel={editavel}/>
                <div className="form-wrapper">
                    <div className="width--95">
                        <DadosVisitante estado={!editavel ? "disabled" : ""} estadoCpf={!editavel ? "disabled" : ""}
                                        cpf={cpf} onCpfValido={onCpfValido} buscarDados={!editavel}/>
                        <hr className="hr--margin-top"/>
                        <RegistroVisitante
                            cadastradoPor={resultadoBusca?.cadastrado_por}
                            cadastradoEm={resultadoBusca?.cadastrado_em}
                            modificadoPor={resultadoBusca?.modificado_por}
                            modificadoEm={resultadoBusca?.modificado_em} />
                    </div>
                </div>
            </form>
        </div>
    )
}

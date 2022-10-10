import React, {useEffect, useRef, useState} from "react";
import {Form} from "react-bootstrap";

import "../assets/css/dados-registro.css"
import * as Funcoes from "../assets/js/modules/dados-visitante"
import useAxiosPrivate from "../hooks/useAxiosPrivate";
import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";

export default function DadosVisitante(props) {
    const axiosPrivate = useAxiosPrivate();
    const handleInvalidSession = useInvalidSessionHandler();
    const [formData, setFormData] = React.useState({
        cpf:"",
        nome:"",
        dataNascimento:"",
        identidade:"",
        expedidor:""
    });

    const cpfAutoFocus = props.cpfAutoFocus ? props.cpfAutoFocus : false;
    const cpfRef = useRef();
    const [disabled, setDisabled] = useState(props.estado?.toLowerCase() === "disabled");
    const [readOnly, setReadOnly] = useState(props.estado?.toLowerCase() === "readonly");
    const [cpfDisabled, setCpfDisabled] = useState(props.estadoCpf?.toLowerCase() === "disabled");
    const [cpfReadOnly, setCpfReadOnly] = useState(props.estadoCpf?.toLowerCase() === "readonly");

    const [placeholder, setPlaceholder] = useState({
        cpf: "Insira o cpf do visitante",
        nome: "Insira o nome do visitante",
        identidade: "Insira o número do documento",
        expedidor: "Insira o órgão expedidor",
    });

    function handleChange(event) {
        const {name, value} = event.target;
        setFormData({...formData, [name]: value});
    }

    function handleCpfChange(event) {
        let cpf = event.target;
        let onCpfValido = setOnCpfValido(cpf);
        event.target.classList.remove("is-invalid");

        cpf.setAttribute("maxlength", "14");
        setTimeout(()=> {
            cpf.value=Funcoes.mascaraCPF(cpf.value);
            if(cpf.value.length === 14) Funcoes.validaCPF(cpf, onCpfValido);
        }, 1);

        setFormData({...formData, cpf: cpf.value});
    }

    function preencherCampos() {
        let onCpfValido = setOnCpfValido(cpfRef.current);
        Funcoes.validaCPF(cpfRef.current, onCpfValido);
    }

    function setOnCpfValido(cpf) {
        let onCpfValido = ()=>{}

        if(props.onCpfValido && props.buscarDados) {
            let codigoResposta;

            onCpfValido = async () => {
                try {
                    const response = await axiosPrivate.get(`/visitante?cpf=${cpf.value}`);
                    if(response.data) {
                        const visitante = response.data;
                        setFormData({cpf: cpf.value,
                            nome: visitante.nome,
                            dataNascimento: visitante.data_nascimento ?? "",
                            identidade: visitante.identidade ?? "",
                            expedidor: visitante.expedidor ?? ""
                        });

                        codigoResposta = response.status;
                        props.onCpfValido(codigoResposta, visitante);
                    }
                } catch (error) {
                    codigoResposta = error.response.status;
                    props.onCpfValido(codigoResposta);

                    if(codigoResposta === 401) {
                        handleInvalidSession();
                    } else if (codigoResposta === 400) {
                        cpf.classList.add("is-invalid");
                    }

                    setFormData({cpf: cpf.value,
                        nome: "",
                        dataNascimento: "",
                        identidade: "",
                        expedidor: ""
                    });
                }
            }
        }
        return onCpfValido;
    }

    useEffect(() => {
        setCpfDisabled(props.estadoCpf?.toLowerCase() === "disabled");
        setCpfReadOnly(props.estadoCpf?.toLowerCase() === "readonly");
        setDisabled(props.estado?.toLowerCase() === "disabled");
        setReadOnly(props.estado?.toLowerCase() === "readonly");
    });

    useEffect(() => {
        if(props.onCpfValido && props.cpf && Funcoes.cpfValido(props.cpf)) {
            setFormData({...formData, cpf: Funcoes.mascaraCPF(props.cpf)});
        }
    },[]);

    useEffect(() => {
        if (!props.cpf) {return;}
        if (disabled || readOnly) {
            if (formData.cpf !== Funcoes.mascaraCPF(props.cpf)) {
                setFormData({...formData, cpf: Funcoes.mascaraCPF(props.cpf)});
            }
            preencherCampos();
            setPlaceholder({
                    cpf: "",
                    nome: "",
                    identidade: "",
                    expedidor: "",
            });
        } else {
            setPlaceholder({
                cpf: "Insira o cpf do visitante",
                nome: "Insira o nome do visitante",
                identidade: "Insira o número do documento",
                expedidor: "Insira o órgão expedidor",
            })
        }
    },[disabled, readOnly]);

    useEffect(() => {
        preencherCampos();
        cpfRef.current?.classList.remove("is-invalid");
    },[formData.cpf]);

    return (
        <>
            <div className="form-linha">
                <Form.Group controlId="cpf" className=" width--33 position-relative">
                    <Form.Label>CPF:</Form.Label>
                    <Form.Control ref={cpfRef} type="text" name="cpf" required autoFocus={cpfAutoFocus} readOnly={cpfReadOnly} disabled={cpfDisabled}
                                  value={formData.cpf} placeholder={placeholder.cpf} onChange={handleCpfChange}/>
                    <div className="invalid-tooltip">CPF não é valido</div>
                </Form.Group>

                <Form.Group controlId="nome" className="campo width--65 position-relative">
                    <Form.Label>Nome:</Form.Label>
                    <Form.Control type="text" name="nome" required readOnly={readOnly} disabled={disabled}
                                  value={formData.nome} placeholder={placeholder.nome} onChange={handleChange}/>
                    <div className="invalid-tooltip">Nome não pode estar vazio</div>
                </Form.Group>
            </div>

            <div className="form-linha">
                <Form.Group controlId="dataNascimento" className="campo width--35">
                    <Form.Label>Data de Nascimento:</Form.Label>
                    <Form.Control type="date" name="dataNascimento" readOnly={readOnly} disabled={disabled}
                                  value={formData.dataNascimento} onChange={handleChange}/>
                </Form.Group>

                <div className="form-linha width--63">
                    <Form.Group controlId="identidade" className="campo width--65">
                        <Form.Label>RG/CNH:</Form.Label>
                        <Form.Control type="text" name="identidade" readOnly={readOnly} disabled={disabled}
                                      value={formData.identidade} placeholder={placeholder.identidade} onChange={handleChange}/>
                    </Form.Group>
                    <Form.Group controlId="expedidor" className="campo width--33">
                        <Form.Label>Expedidor:</Form.Label>
                        <Form.Control type="text" name="expedidor" readOnly={readOnly} disabled={disabled}
                                      value={formData.expedidor} placeholder={placeholder.expedidor} onChange={handleChange}/>
                    </Form.Group>
                </div>
            </div>
        </>
    )
}

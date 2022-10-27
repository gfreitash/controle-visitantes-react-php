import React, {useState} from "react";
import {Form, Modal} from "react-bootstrap";
import useAxiosPrivate from "../hooks/useAxiosPrivate";
import fileDownload from "js-file-download";
import * as Funcoes from "../assets/js/modules/dados-visitante";
import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";

export async function emitirRelatorioVisita({axios, dataInicio, dataFim, cpf, apenasAbertas}) {
    let url = "/relatorio?relatorio=visitas";
    if (apenasAbertas) {
        url += "&status=abertas";
    } else {
        url += "&status=realizadas";
    }
    if (cpf) {
        url += `&cpf=${cpf}`;
    }
    if (dataInicio) {
        url += `&dataInicio=${dataInicio}`;
    }
    if (dataFim) {
        url += `&dataFim=${dataFim}`;
    }

    const resposta = await axios.get(url, {responseType: "blob"});
    let nomeArquivo =  resposta.headers['content-disposition'].split("filename=")[1];
    nomeArquivo = decodeURI(nomeArquivo);

    fileDownload(resposta.data, nomeArquivo, "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
}

export default function ModalRelatorioVisitas({exibir, onFechar}) {
    const axios = useAxiosPrivate();
    const handleInvalidSession = useInvalidSessionHandler();

    const [form, setForm] = useState({
        dataInicio: "",
        dataFim: "",
        visitante: "",
        apenasAbertas: false
    });

    const [checks, setChecks] = useState({
        dataInicio: false,
        dataFim: false,
        visitante: false
    });

    const limparForm = () => {
        setForm({
            dataInicio: "",
            dataFim: "",
            visitante: "",
            apenasAbertas: false
        });
        setChecks({
            dataInicio: false,
            dataFim: false,
            visitante: false
        });
    }

    const handleFechar = () => {
        onFechar(false);
        limparForm();
    }

    const handleCpfChange = (e) => {
        e.target.classList.remove("is-invalid", "is-valid");
        e.target.setAttribute("maxlength", "14");
        let cpf = Funcoes.mascaraCPF(e.target.value);
        setForm({...form, visitante: cpf});

        if (cpf.length === 14) {
            if(Funcoes.cpfValido(cpf)) {
                e.target.classList.add("is-valid");
            } else {
                e.target.classList.add("is-invalid");
            }
        }
    }

    const handleEmissao = async () => {
        const cpfValido = checks.visitante && Funcoes.cpfValido(form.visitante);
        const args = {
            axios: axios,
            apenasAbertas: form.apenasAbertas,
            dataInicio: checks.dataInicio ? form.dataInicio : null,
            dataFim: checks.dataFim ? form.dataFim : null,
            cpf: cpfValido ? form.visitante : null
        }
        console.log("Args: ", args);
        console.log("Form: ", form);
        try {
            await emitirRelatorioVisita(args);
            handleFechar();
        } catch (error) {
            if (error.response.status === 401) {
                handleInvalidSession();
            }
        }
    }

    return (
        <Modal
            size="lg"
            show={exibir}
            onHide={handleFechar}
            backdrop="static"
            keyboard={false}
        >
            <Modal.Header closeButton>
                <Modal.Title>Emitir relatório de visitas</Modal.Title>
            </Modal.Header>

            <Modal.Body>
                <form className="d-flex align-items-center justify-content-between" id="relatorioVisitas">
                    <div className="d-flex">
                        <Form.Check name="apenasAbertas" id="apenasAbertas"
                                    checked={form.apenasAbertas} type="checkbox" className="me-2"
                                    onChange={(event) => setForm({...form, apenasAbertas: event.target.checked})}/>
                        <label htmlFor="apenasAbertas">Apenas abertas?</label>
                    </div>

                    <div className="d-flex flex-column">
                        <div className="d-flex flex-row">
                            <Form.Check name="dataInicioCheck" id="dataInicioCheck"
                                        checked={checks.dataInicio} type="checkbox" className="me-2"
                                        onChange={(event) => setChecks({...checks, dataInicio: event.target.checked})}/>
                            <label htmlFor="dataInicioCheck">Data Início?</label>
                        </div>
                        <div className="d-flex flex-row">
                            <Form.Control type="date" disabled={!checks.dataInicio} value={form.dataInicio}
                                          onChange={(event)=>setForm({...form, dataInicio: event.target.value})}/>
                        </div>
                    </div>
                    <div className="d-flex flex-column">
                        <div className="d-flex flex-row">
                            <Form.Check name="dataFimCheck" id="dataFimCheck"
                                        checked={checks.dataFim} type="checkbox" className="me-2"
                                        onChange={(event) => setChecks({...checks, dataFim: event.target.checked})}/>
                            <label htmlFor="dataFimCheck">Data Fim?</label>
                        </div>
                        <div className="d-flex flex-row">
                            <Form.Control type="date" disabled={!checks.dataFim} value={form.dataFim}
                                          onChange={(event)=>setForm({...form, dataFim: event.target.value})}/>
                        </div>
                    </div>

                    <div className="d-flex flex-column">
                        <div className="d-flex flex-row">
                            <Form.Check name="visitanteCheck" id="visitanteCheck"
                                        checked={checks.visitante} type="checkbox" className="me-2"
                                        onChange={(event) => setChecks({...checks, visitante: event.target.checked})}/>
                            <label htmlFor="visitanteCheck">Buscar por Visitante?</label>
                        </div>
                        <Form.Control disabled={!checks.visitante} value={form.visitante}
                                      onChange={handleCpfChange}/>
                    </div>
                </form>
            </Modal.Body>

            <Modal.Footer>
                <button className="btn btn-secondary" onClick={handleFechar}>Cancelar</button>
                <button className="btn btn-primary" onClick={handleEmissao}>Emitir</button>
            </Modal.Footer>
        </Modal>

    )
}

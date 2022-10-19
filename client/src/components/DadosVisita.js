import React, {useEffect, useState} from "react";
import {Form} from "react-bootstrap";
import useAxiosPrivate from "../hooks/useAxiosPrivate";

export default function DadosVisita(props) {
    const axios = useAxiosPrivate();

    const [form, setForm] = useState({
        id: props.id ?? "",
        salaVisita: "",
        motivoVisita: "",
        foiLiberado: -1
    });

    const foi_liberado = {
        flexGrow: "1",
        display: "flex",
        alignItems: "center"
    }

    const handleChange = (event) => {
        setForm({
            ...form,
            [event.target.name]: event.target.value
        });
    }

    const handleFoiLiberado = (event) => {
        setForm({
            ...form,
            foiLiberado: parseInt(event.target.value)
        });
    }

    useEffect(()=> {
        if (form.id) {
            let isMounted = true;
            let controlador = new AbortController();

            const obterVisita = async () => {
                try {
                    const resposta = await axios.get(`/visita?id=${form.id}`, {signal: controlador.signal});

                    if (resposta.status === 200) {
                        setForm({
                            id: resposta.data.id,
                            salaVisita: resposta.data.sala_visita ?? "",
                            motivoVisita: resposta.data.motivo_visita ?? "",
                            foiLiberado: resposta.data.foi_liberado ?? -1
                        })
                        props.onVisitaEncontrada && props.onVisitaEncontrada(resposta.data);
                    }
                } catch (error) {
                    if (error.code === "ERR_CANCELED")
                        return;
                    console.log(error);
                }
            }

            isMounted && obterVisita();

            return () => {
                isMounted = false;
                controlador.abort();
            }
        }
    }, [props.id, props.disabled, props.status]);

    return (
        <div className="form-linha">
            <div className="form-linha width--80">
                <Form.Group controlId="salaVisita" className="campo width--33">
                    <Form.Label>Onde vai:</Form.Label>
                    <Form.Control value={form.salaVisita} type="text" name="salaVisita" onChange={handleChange}
                                  disabled={props.disabled} required/>
                </Form.Group>

                <Form.Group controlId="motivoVisita" className="campo width--65">
                    <Form.Label>Motivo da visita:</Form.Label>
                    <Form.Control value={form.motivoVisita} type="text" name="motivoVisita" onChange={handleChange}
                                  disabled={props.disabled}/>
                </Form.Group>
            </div>

            <div className="campo width--18">
                <label className="form-label">Foi liberado?</label>
                <div key="inline-radio" style={foi_liberado}>
                    <Form.Check inline required label="Sim" name="foiLiberado" checked={form.foiLiberado === 1}
                                type="radio" id="radio-sim" value="1" disabled={props.disabled} onChange={handleFoiLiberado}/>
                    <Form.Check inline required label="Não" name="foiLiberado" checked={form.foiLiberado === 0}
                                type="radio" id="radio-nao" value="0" disabled={props.disabled} onChange={handleFoiLiberado}/>
                </div>
            </div>
        </div>
    )
}

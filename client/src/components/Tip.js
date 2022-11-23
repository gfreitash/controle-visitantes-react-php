import React from "react";
import * as Tooltip from "@radix-ui/react-tooltip";
import "../assets/css/tip.css";

export default function Tip({trigger, label, ponta, lado, cursor}) {
    const side = lado ?? "bottom";
    return (
        <Tooltip.Provider delayDuration={300} skipDelayDuration={200}>
            <Tooltip.Root>
                <Tooltip.Trigger asChild>
                    <button className={`blankButton ${cursor ?? ""}`}>{trigger}</button>
                </Tooltip.Trigger>
                <Tooltip.Portal>
                    <Tooltip.Content className="TooltipContent" side={side} sideOffset={9}>
                        {label}
                        {ponta && (<Tooltip.Arrow className="TooltipArrow" />)}
                    </Tooltip.Content>
                </Tooltip.Portal>
            </Tooltip.Root>
        </Tooltip.Provider>
    )
}

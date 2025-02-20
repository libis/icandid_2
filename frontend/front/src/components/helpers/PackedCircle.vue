<template>
<div>
<svg width="700" height="700">
<g
class="flower"
v-for="datum in layoutData.children"
:key="datum.data.name"
:style="{
transform: `translate(${datum.x}px, ${datum.y}px)`
}"
>
<circle class="flower__circle" :r="datum.r" :fill="datum.data.color"></circle>
<text class="flower__label">
    <tspan>{{ datum.data.name }}</tspan>
    <tspan x="0" dy="18">({{ datum.data.amount }})</tspan>
</text>
</g>
</svg>
</div>
</template>

<script>
import { hierarchy, pack } from '../../../node_modules/d3-hierarchy/build/d3-hierarchy'
export default {
    name: "PackedCircle",
    props: ["data"],    
    computed: {
        transformedData() {
            return {
                name: 'Top Level',
                children: this.data.map(datum => ({
                    ...datum,
                size: datum.amount,
                parent: 'Top Level'
                }))
            }
        },
        layoutData() {
            // Generate a D3 hierarchy
            const rootHierarchy = hierarchy(this.transformedData)
            .sum(d => d.size)
            .sort((a, b) => {
            return b.value - a.value
        })

        // Pack the circles inside the viewbox
        return pack()
            .size([700, 700])
            .padding(2)(rootHierarchy)
        }
    }
}
</script>

<style>
xbody {
font: 16px -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica,
Arial, sans-serif;
}

svg {
display: block;
margin: 0 auto;
height:100%
}

.flower {
transition: transform 0.2s ease-in-out;
text-anchor: middle;
}

.flower__circle {
transition: r 0.2s ease-in-out;
}

.flower__label {
fill: #fff;
font-weight: bold;
text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.controls {
display: flex;
justify-content: center;
margin-top: 20px;
}

.control {
display: inline-flex;
flex-direction: column;
margin: 0 4px;
}

.control xlabel {
font-size: 14px;
font-weight: bold;
margin-bottom: 4px;
}

.control input {
display: block;
font: inherit;
width: 100px;
}
</style>
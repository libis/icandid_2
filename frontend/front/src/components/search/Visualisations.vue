<template>
    <div>
        <div class="tabs is-boxed">
        <ul>
            <li v-bind:class="{'is-active': (selectedTab=='treemap')}">
                <a @click.prevent.stop="selectedTab='treemap'">{{ $ml.get('treemap') }}</a>
            </li>
            <li v-bind:class="{'is-active': (selectedTab=='bargraph')}">
                <a @click.prevent.stop="selectedTab='bargraph'">{{ $ml.get('bargraph') }}</a>
            </li>
            <li v-bind:class="{'is-active': (selectedTab=='heatmap')}">
                <a @click.prevent.stop="selectedTab='heatmap'">{{ $ml.get('heatmap') }}</a>
            </li>
            <li v-bind:class="{'is-active': (selectedTab=='bubblechart')}">
                <a @click.prevent.stop="selectedTab='bubblechart'">{{ $ml.get('bubblechart') }}</a>
            </li>
            <li v-bind:class="{'is-active': (selectedTab=='wordcloudner')}">
                <a @click.prevent.stop="selectedTab='wordcloudner'">{{ $ml.get('wordcloudner') }}</a>
            </li>
            <li v-bind:class="{'is-active': (selectedTab=='top10')}">
                <a @click.prevent.stop="selectedTab='top10'">{{ $ml.get('top10') }}</a>
            </li>
            <li v-bind:class="{'is-active': (selectedTab=='network')}">
                <a @click.prevent.stop="selectedTab='network'">{{ $ml.get('network') }}</a>
            </li>
            
            
        </ul>
        </div>
        <div class="content" v-bind:class="{'is-hidden': (selectedTab!='top10')}">
            <Top10 ref="top10"></Top10>
        </div>
        <div class="content" v-bind:class="{'is-hidden': (selectedTab!='treemap')}">
            <Treemap ref="treemap" :activetab="selectedTab+$parent.mode"></Treemap>
        </div>
        <div class="content" v-bind:class="{'is-hidden': (selectedTab!='bubblechart')}">
            <BubbleChart ref="bubblechart"></BubbleChart>
        </div>
        <div class="content" v-bind:class="{'is-hidden': (selectedTab!='wordcloudner')}">
            <WordCloudNer ref="wordcloudner"></WordCloudNer>
        </div>
        <div class="content" v-bind:class="{'is-hidden': (selectedTab!='bargraph')}">
            <BarGraph ref="bargraph"></BarGraph>
        </div>
        <div class="content" v-bind:class="{'is-hidden': (selectedTab!='heatmap')}">
            <Heatmap ref="heatmap"></Heatmap>
        </div>
        <div class="content" v-bind:class="{'is-hidden': (selectedTab!='network')}">
            <Network ref="network"></Network>
        </div>

    </div>
</template>
<script>
import Top10 from "./visualisation/Top10.vue";
import Treemap from "./visualisation/Treemap.vue";
import BubbleChart from "./visualisation/BubbleChart.vue";
import WordCloudNer from "./visualisation/WordCloudNer.vue";
import BarGraph from "./visualisation/BarGraph.vue";
import Heatmap from "./visualisation/Heatmap.vue";
import Network from "./visualisation/Network.vue";

export default {
    data() {
        return {
            selectedTab:'treemap'
        }
    },
    watch:{
        selectedTab: function() {
            if (this.selectedTab == 'network') {
                this.$refs.network.update()
            }
        }
    },
    methods:{
        clear() {
            this.$refs.top10.clear();
            this.$refs.treemap.clear();
            this.$refs.bubblechart.clear();
            this.$refs.wordcloudner.clear();
            this.$refs.bargraph.clear();            
            this.$refs.heatmap.clear();
            this.$refs.network.clear();
            this.selectedTab = 'treemap'
        }
    },
    components: {
        Top10,
        Treemap,
        BubbleChart,
        WordCloudNer,
        BarGraph,
        Heatmap,
        Network
    }
}
</script>
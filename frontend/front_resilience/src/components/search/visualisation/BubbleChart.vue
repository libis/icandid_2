<template>
    <div>
        <Loader v-if="loading" style="margin:100px"></Loader>
        <PackedCircle v-if="!loading" :data="authors"></PackedCircle>
        <p></p><p v-html="$ml.get('bubblechart_info')"></p>
    </div>
</template>
<script>
import Loader from '../../helpers/Loader.vue'
import PackedCircle from "../../helpers/PackedCircle.vue";
import axios from "axios";
import { mapGetters } from "vuex";
axios.defaults.withCredentials = true;
export default {
    data() {
        return {
            backgroundColors : [
                "#3366CC",
                "#DC3912",
                "#FF9900",
                "#109618",
                "#990099",
                "#3B3EAC",
                "#0099C6",
                "#DD4477",
                "#66AA00",
                "#B82E2E",
                "#316395",
                "#994499",
                "#22AA99",
                "#AAAA11",
                "#6633CC",
                "#E67300",
                "#8B0707",
                "#329262",
                "#5574A6",
                "#651067"
            ],
            aggs: {
                "author": {
                    "terms": {
                        "field": "creator.name.keyword",
                        "order": {
                        "_count": "desc"
                        },
                        "size": 15
                    }
                }
            },
            authors:[
                {
                name: '',
                amount: 0,
                color: '#FFFFFF'
                }
                ],
            data:[],
            loading:false
        }
    },
    components: {
        PackedCircle,
        Loader
    },
    created() {
        this.update();
    },
    computed: mapGetters(['getApiQueryUrl','getElasticQuery']),
    watch:{
      getElasticQuery: function() { this.update() ;}
    },
    methods: {
        clear() {
            this.authors = []
        },
        update() {
            this.loading = true
            var es_query = JSON.parse(JSON.stringify(this.getElasticQuery))
            if (es_query == "") return false
            es_query.size = 0
            es_query.from = 0
            es_query.aggs = this.aggs
            axios
                .post(this.getApiQueryUrl, es_query)
                .then(res => {
                    this.data = res.data.aggregations;
                    this.calculateChartData();
                    this.loading = false
            
                })
                /*.catch(error => console.log(error))*/;
        },
        newinput() {
            this.update()
        },
        calculateChartData() {
            this.authors = [];
            for(var i=0;i<this.data.author.buckets.length;i++) {
                var author = {}
                author.name = this.data.author.buckets[i].key;
                author.amount = this.data.author.buckets[i].doc_count;
                author.color = this.backgroundColors[i];

                this.authors.push(author);
            }
        }
    }
}
</script>
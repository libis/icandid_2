<template>
    <div>
        <h1 class="title">{{ current.name }}</h1>
        <table border=0 cellspacing=1 cellpadding=0 width=100%>
            <tr v-if="current.description != '' && current.description != null">
                <td class="desc" nowrap>{{ $ml.get('description') }}: </td>
                <td style="white-space: pre-line">{{ current.description }}</td>
            </tr>
            <tr v-if="current.provider != '' && current.provider != null">
                <td class="desc" nowrap>{{ $ml.get('provider') }} : </td>
                <td style="white-space: pre-line">{{ current.provider }}</td>
            </tr>
            <tr v-if="current.languages != '' && current.languages != null">
                <td class="desc" nowrap>{{ $ml.get('talen') }} : </td>
                <td>{{ joinLabels(current.languages) }}</td>                                        
            </tr>
            <tr v-if="current.from != '' && current.from != null">
                <td class="desc" nowrap>{{ $ml.get('period') }} : </td>
                <td v-html="periodForm(current.from,current.until)">
                    
                </td>
            </tr>
            <tr v-if="current.query != '' && current.query != null">
                <td class="desc" nowrap>{{ $ml.get('query') }} : </td>
                <td>{{ current.query }}</td>                                        
            </tr>

            <tr v-if="current.license != '' && current.license != null">
                <td class="desc" nowrap>{{ $ml.get('license') }} : </td>
                <td style="white-space: pre-line" v-linkified>{{ current.license }}</td>
            </tr>
            <tr>
                <td class="desc" nowrap><span v-if="current.recordcount > 0">{{ $ml.get('size') }} : </span></td>
                <td><span v-if="current.recordcount > 0">{{ amount(current.recordcount) }} {{$ml.get('documents')}} ({{$ml.get('lastcount')}} {{ current.recordcountdate}})</span></td>                                        
            </tr>
            <tr v-if="current.available==0">
                <td class="desc" nowrap>{{ $ml.get('status') }} : </td>
                <td >
                    <span  style="color:Red; font-weight:Bold; text-align:left">
                        {{ $ml.get('unavailable') }}
                    </span>
                </td>                                        
            </tr>
        </table>
    </div>
</template>
<script>
import axios from "axios";
import { mapGetters} from "vuex";
axios.defaults.withCredentials = true;
export default {
    data() {
        return {
            current:[]
        }
    },
    components:{
    },
    created(){
        this.update();
    },    
    methods: {
        update() {
            this.current = []
            axios
                .get(this.getApiCollectionUrl + '/byid/' + this.$route.params.id)
                .then(res => {
                    this.current = res.data;
                })
                .catch(error => {
                    console.log(error)
                });                    
        },
        periodForm(f,u) {
            var o = f + ' <i class="fa fa-long-arrow-right" aria-hidden="true"></i> ';
            var vandaag = this.$moment(new Date()).format('YYYY-MM-DD');
            if (u < vandaag) {
                o += u; 
            } else {
                o += "..."
            }
            return o;
        },
        amount(i) {
            var nf = Intl.NumberFormat();
            return nf.format(i);
        },
        joinLabels(l) {
            var a = [];
            for(var i=0; i<l.length; i++) {
                a.push(l[i]["name_"+this.$ml.current]);
            }
            return a.join(', ');
        },
    },
    computed: mapGetters(['getApiCollectionUrl'])
}
</script>
<style scoped>
.unavail {
    font-style: italic;
    color:gray;  
}
.vertalign {
  display: inline-block;
  vertical-align: middle;
  line-height: normal;  
  padding-top:3px;
  padding-right:3px
}
.desc {
    font-weight:bold
}
.extraspace {
    margin-right:6px
}
</style>
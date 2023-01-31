<template>
    <div>
        <h1 class="title">{{ $ml.get('collections') }}</h1>
        <div class="box" v-if="Object.keys(options).length>0">
            <input type="text" style="width:100%" class="input" v-model="search.term" :placeholder="$ml.get('search')+'...'" /><br/><br/>
            <div class="columns" style="margin-top:-25px">
                <div class="column is-1" style="white-space: nowrap;padding-top:16px">
                    <label class="label">{{ $ml.get("language") }} : </label>
                </div>
                <div class="column">
                    <div class="field is-grouped is-grouped-multiline">
                    <p class="control" v-for="lang in options.languages" :key="lang.id" style="margin:0px">
                        <input
                        class="is-checkradio is-small"
                        :id="lang.isocode"
                        type="checkbox"
                        :name="lang.isocode"
                        :value="lang.isocode"
                        v-model="search.lang"
                        />
                        <label :for="lang.isocode" style="white-space: nowrap">{{ lang["name_" + $ml.current] }}</label>
                    </p>
                    </div>
                </div>
            </div>

            <div class="columns" style="margin-top:-25px">
                <div class="column is-1" style="white-space: nowrap;padding-top:16px">
                    <label class="label">{{ $ml.get("labels") }} : </label>
                </div>
                <div class="column">
                    <div class="field is-grouped is-grouped-multiline">
                    <p class="control" v-for="label in options.labels" :key="label.id" style="margin:0px">
                        <input
                        class="is-checkradio is-small"
                        :id="label.id"
                        type="checkbox"
                        :name="label.id"
                        :value="label.id"
                        v-model="search.label"
                        />
                        <label :for="label.id" style="white-space: nowrap">{{ label["name_" + $ml.current] }}</label>
                    </p>
                    </div>
                </div>
            </div>

            <div class="columns" style="margin-top:-25px">
                <div class="column is-1" style="white-space: nowrap;padding-top:16px">
                    <label class="label">{{ $ml.get("provider") }} : </label>
                </div>
                <div class="column">
                    <div class="field is-grouped is-grouped-multiline">
                    <p class="control" v-for="(provider,idx) in options.providers" :key="'provider_'+idx" style="margin:0px">
                        <input
                        class="is-checkradio is-small"
                        :id="'provider_'+idx"
                        type="checkbox"
                        :name="'provider_'+idx"
                        :value="provider.provider"
                        v-model="search.provider"
                        />
                        <label :for="'provider_'+idx" style="white-space: nowrap">{{ provider.provider }}</label>
                    </p>
                    </div>
                </div>
            </div>

            <div class="columns" style="margin-top:-25px">
                <div class="column is-1" style="white-space: nowrap;padding-top:14px">
                    <label class="label">{{ $ml.get("period") }} : </label>
                </div>
                <div class="column" >
                        <span class="vertalign" style="font-size:12px">{{ $ml.get('from') }} : </span><input type="date" class="input" style="width:150px" v-model="search.from" name="dp_fromdate" />
                        <span class="vertalign" style="margin-left:5px;font-size:12px">{{ $ml.get('toandincluding') }} : </span><input type="date" class="input" style="width:150px" v-model="search.until" name="dp_untildate" />
                        <button class="button" @click="cleardates()" style="margin-left:15px;font-size:12px;margin-top:6px;height:24px">{{ $ml.get('cleardates') }}</button>
                </div>
            </div>

            <div class="columns" style="margin-top:-25px">
                <div class="column is-1" style="white-space: nowrap;padding-top:16px">
                  
                </div>
                <div class="column">
                    <p class="control" style="margin:0px">
                        <input
                        class="is-checkradio is-small"
                        id="available"
                        type="checkbox"
                        name="available"
                        value="1"
                        v-model="search.available"
                        />
                        <label for="available" style="white-space: nowrap">{{ $ml.get('showonlyavailablecollections') }}</label>
                    </p>
                </div>
            </div>    


        </div>

        <Loader v-if="loading"></Loader>


        <button v-if="this.data.filter(el => el.selected == true).length > 0" class="button" @click="requestAccess()" style="float: right;margin-bottom:12px">{{ $ml.get('requestdatasetaccess') }}</button>
        <table class="table is-fullwidth" style="margin-bottom:0px" v-if="data.length>0">
            <tr>
                <th width="4%"><i class="fa fa-caret-right" style="font-size:20px;visibility:hidden" aria-hidden="true" ></i></th>
                <th width="3%">&nbsp;</th>
                <th width="47%">{{ $ml.get('fullname') }}</th>
                <th width="46%">{{ $ml.get('labels') }}</th>
            </tr>
        </table>
        <table class="table is-fullwidth" v-if="data.length>0">
            <tr v-for="(d,idx) in data" :key="idx">
                <td colspan="4" width="100%" style="padding:0px;">
                    <table class="table" width="100%">
                        <tr >
                            <td style="border-bottom:0px" width="4%">
                                    <i class="fa" style="cursor:pointer;font-size:20px" :class="(d.show?'fa-caret-down':'fa-caret-right')" aria-hidden="true" @click="toggle(idx)"></i>
                            </td>
                            <td style="border-bottom:0px" width="3%">
                                <i class="fa" style="cursor:pointer;font-size:20px" :class="(hasAccess(d.internalident)?' fa-unlock has-text-success':'fa-lock has-text-danger extraspace')" aria-hidden="true"   @click="toggle(idx)"></i>                            
                            </td>
                            <td  style="cursor:pointer;border-bottom:0px" width="47%" :class="(d.available==1?'':'unavail')" @click="toggle(idx)">
                                {{ d.name }}
                            </td>
                            <td style="border-bottom:0px" width="46%" :class="(d.available==1?'':'unavail')">
                                {{ joinLabels(d.labels) }}
                            </td>
                        </tr>
                        <tr v-if="d.show">
                            <td colspan="4" style="border-bottom:0px;font-size:12px;padding-left:30px">
                                <table border=0 cellspacing=1 cellpadding=0 width=100%>
                                    <tr v-if="d.description != '' && d.description != null">
                                        <td class="little" nowrap>{{ $ml.get('description') }}: </td>
                                        <td class="little" style="white-space: pre-line">{{ d.description }}</td>
                                    </tr>
                                    <tr v-if="d.provider != '' && d.provider != null">
                                        <td class="little" nowrap>{{ $ml.get('provider') }} : </td>
                                        <td class="little" style="white-space: pre-line">{{ d.provider }}</td>
                                    </tr>
                                    <tr v-if="d.languages != '' && d.languages != null">
                                        <td class="little" nowrap>{{ $ml.get('talen') }} : </td>
                                        <td class="little">{{ joinLabels(d.languages) }}</td>                                        
                                    </tr>
                                    <tr v-if="d.from != '' && d.from != null">
                                        <td class="little" nowrap>{{ $ml.get('period') }} : </td>
                                        <td class="little" v-html="periodForm(d.from,d.until)">
                                            
                                        </td>
                                    </tr>
                                    <tr v-if="d.query != '' && d.query != null">
                                        <td class="little" nowrap>{{ $ml.get('query') }} : </td>
                                        <td class="little">{{ d.query }}</td>                                        
                                    </tr>

                                    <tr v-if="d.license != '' && d.license != null">
                                        <td class="little" nowrap>{{ $ml.get('license') }} : </td>
                                        <td class="little" style="white-space: pre-line" v-linkified>{{ d.license }}</td>
                                    </tr>
                                    <tr>
                                        <td class="little" nowrap><span v-if="d.recordcount > 0">{{ $ml.get('size') }} : </span></td>
                                        <td class="little"><span v-if="d.recordcount > 0">{{ amount(d.recordcount) }} {{$ml.get('documents')}} ({{$ml.get('lastcount')}} {{d.recordcountdate}})</span></td>                                        
                                        <td class="little" width="40%">
                                            <span v-if="d.available==0" style="color:Red; font-weight:Bold; text-align:left">
                                                {{ $ml.get('unavailable') }}
                                            </span>
                                            <span v-else-if="hasAccess(d.internalident)">{{$ml.get('youalreadyhaveaccess') }}</span>
                                            <span v-else>
                                                <p class="control" style="margin:0px">
                                                    <input :id="'sel_'+d.id" type="checkbox" class="is-checkradio is-small" v-model="d.selected">
                                                    <label style="white-space: nowrap" :for="'sel_'+d.id">{{ $ml.get('requestdatasetaccess') }}</label>
                                                </p>
                                            </span>
                                        </td>                                        
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table> 
        <button v-if="this.data.filter(el => el.selected == true).length > 0" class="button" @click="requestAccess()" style="float: right;margin-top:-7px">{{ $ml.get('requestdatasetaccess') }}</button>
        <br/><br/>
    </div>
</template>
<script>
import axios from "axios";
import { mapGetters, mapActions } from "vuex";
axios.defaults.withCredentials = true;
import Loader from './helpers/Loader.vue'

export default {
    data() {
        return {
            search: {
                term:"",
                lang:[],
                label:[],
                available:1,
                provider:[],
                from:"",
                until:""
            },
            options: [],
            data:[],
            loading:false
        }
    },
    components:{
        Loader
    },    
    methods: {
        ...mapActions(['setSelectedDatasets']),
        update() {
            this.data = []

            this.loading = true
            axios
                .post(this.getApiCollectionUrl + '/search', this.search)
                .then(res => {
                    this.data = []
                    for (var i=0; i<res.data.length; i++) {
                        res.data[i].show = false;
                        res.data[i].selected = false;
                        this.data.push(res.data[i]);
                    }
                    this.loading = false
                })
                .catch(error => {
                    console.log(error)
                    this.loading = false
                });                    
        },
        joinLabels(l) {
            var a = [];
            for(var i=0; i<l.length; i++) {
                a.push(l[i]["name_"+this.$ml.current]);
            }
            return a.join(', ');
        },
        cleardates() {
            this.search.from='';
            this.search.until=''
        },
        toggle(idx) {
            this.data[idx].show = !this.data[idx].show;
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
        hasAccess(ident) {
            for(var i=0;i<this.getDatasets.length;i++) {
                if (this.getDatasets[i].internalident == ident) {
                    return true
                }
            }
            return false
        },
        requestAccess() {
            this.setSelectedDatasets(this.data.filter(el => el.selected == true))
            this.$router.push({path:'/request/dataset'});
        }
    },
    computed: mapGetters(['getApiCollectionUrl','getDatasets']),
    created() {
        axios
            .get(this.getApiCollectionUrl + '/options')
            .then(res => {
                this.options = res.data;
            })
            .catch(error => console.log(error));  
            this.update();                  
    },
    watch : {
        search:{
            deep:true,
            handler() {
                setTimeout(this.update(), 1000);
            }
        }
    }
    
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
.little {
    border-bottom:0px;
    padding:0px;
    padding-left:10px
}
.extraspace {
    margin-right:6px
}
</style>
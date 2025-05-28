<template>
    <div>
        <div class="columns" style="margin-bottom:0px" v-if="getHits>0">
            <div class="column is-3" style="padding-top:30px">
                <div v-if="getHits>0">{{ $ml.get('hits') }}: {{ getHits }}</div>
            </div>
            <div class="column is-9 has-text-right">
               <!-- <button class="button is-rounded" :title="$ml.get('selectall')" @click="selectall()" v-if="$parent.mode=='result'"><i class="fa fa-check" style="color:#1a8d00" aria-hidden="true"></i></button> -->
               <button class="button is-rounded" :title="$ml.get('visualizations')" @click="openvisualisation()" v-if="$parent.mode=='result'"><i class="fa fa-bar-chart" aria-hidden="true"></i></button>
                <button class="button is-rounded" :title="$ml.get('searchresult')" @click="closevisualisation()" v-if="$parent.mode!='result'"><i class="fa fa-list" aria-hidden="true"></i></button>
                <button class="button is-rounded" :title="$ml.get('saveiteminset')" @click="opensaveitem()" v-if="$parent.mode=='result'"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                <!-- 
                    <button class="button is-rounded" :title="$ml.get('exportcsv')" @click="exportResult('csv')" v-if="$parent.mode=='result' && this.getPermissions.indexOf('export')>=0"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i>.csv</button>
                    <button class="button is-rounded" :title="$ml.get('exportxlsx')" @click="exportResult('xlsx')" v-if="$parent.mode=='result' && this.getPermissions.indexOf('export')>=0"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i>.xlsx</button>
                    <button class="button is-rounded" :title="$ml.get('exporttxt')" @click="exportResult('txt')" v-if="$parent.mode=='result' && this.getPermissions.indexOf('export')>=0"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i>.txt</button>
                    <button class="button is-rounded" :title="$ml.get('exportjson')" @click="exportResult('jsonld')" v-if="$parent.mode=='result' && this.getPermissions.indexOf('export')>=0"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i>.json-ld</button>
                -->
                <button class="button is-rounded" :title="$ml.get('export')" @click="exportDialog()" v-if="$parent.mode=='result' && this.getPermissions.indexOf('export')>=0"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i><i class="fa fa-long-arrow-right" aria-hidden="true"></i></button>
            </div>
        </div>
        <SaveItem ref="saveitem"></SaveItem>
        <Dialog ref="dialog"></Dialog>
        <ExportDialog ref="exportdialog" @answer="exportDialogResponse"></ExportDialog>
    </div>
</template>
<script>
import SaveItem from './SaveItem.vue'
import Dialog from '../helpers/Dialog.vue'
import ExportDialog from './ExportDialog.vue'
import axios from 'axios';
import { mapGetters } from 'vuex';
export default {
    computed: mapGetters(['getHits','getSearchRequest','getApiProfileUrl','getSelectedResults','getPermissions']),
    components: {
        SaveItem,
        Dialog,
        ExportDialog
    },
    methods: {
        opensaveitem() {
        this.$refs.saveitem.open();    
        },
        exportDialog(){
            this.$refs.exportdialog.open(this.getHits)
        },
        exportDialogResponse(p) {
            if(p.commit) {
                this.exportResult(p.format,p.enrichments)
            }
        },
        exportResult(format, enrichments) {
          var url = this.getApiProfileUrl + '/queue'
          var request = {query:this.getSearchRequest, format:format, enrichments:enrichments, language: this.$ml.current }
          axios
            .post(url, request)
            .then(response => {
                window._paq.push(['trackEvent','Export',"CreateExport"])
                this.$refs.dialog.alert(this.$ml.get('info'),this.$ml.with('email', response.data.email).get('exportinprogress'))
          })
            .catch(error => {
              console.log(error);
          });
        },
        openvisualisation() {
            window._paq.push(['trackEvent','Visualisation',"ShowVisualisation"])
            this.$parent.mode='visualisation'
        },
        closevisualisation() {
            this.$parent.mode='result'
        },
        selectall() {
            this.$parent.selectall();
        }
    }
}
</script>
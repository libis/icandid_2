<template>
    <div>
        <div class="columns">
            <div class="column">
            </div>
            
            <div class="column has-text-right">
<!--                <button class="button is-rounded" :title="$ml.get('exportsetcsv')" @click="exportSet('csv')"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i>.csv</button>
                <button class="button is-rounded" :title="$ml.get('exportsetxlsx')" @click="exportSet('xlsx')"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i>.xslx</button>
                <button class="button is-rounded" :title="$ml.get('exportsettxt')" @click="exportSet('txt')"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i>.txt</button>
                <button class="button is-rounded" :title="$ml.get('exportsetjson')" @click="exportSet('json')"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i>.json-ld</button>
            -->
                <button class="button is-rounded" :title="$ml.get('export')" @click="exportDialog()" v-if="this.getPermissions.indexOf('export')>=0"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i><i class="fa fa-long-arrow-right" aria-hidden="true"></i></button>
            </div>
        </div>
        <Dialog ref="dialog"></Dialog>
        <ExportDialog ref="exportdialog" @answer="exportDialogResponse"></ExportDialog>
    </div>
</template>
<script>
import axios from 'axios';
import { mapGetters } from 'vuex';
import Dialog from '../helpers/Dialog.vue'
import ExportDialog from '../search/ExportDialog.vue'
export default {
    computed: mapGetters(['getHits','getSearchRequest','getApiProfileUrl','getPermissions']),
    components: {
        Dialog,
        ExportDialog
    },
    props:['shelfId'],
    methods: {
        exportDialog(){
            this.$refs.exportdialog.open(this.$parent.activeShelf.length, true)
        },
        exportDialogResponse(p) {
          if (p.commit) {
            this.exportSet(p.format, p.enrichments)
          }
        },
        exportSet(format, enrichments) { 
          var url = this.getApiProfileUrl + '/queue'
          var request = {shelfid:this.shelfId, format:format, enrichments:enrichments, language: this.$ml.current }
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
        getEnrichments() {
          return this.$parent.getEnrichments();
        }
        /*
        exportSet(format) {
          var url = this.getApiProfileUrl + '/shelf/' + this.shelfId + '/' + format
          axios
            .get(url, {responseType: 'blob'})
            .then(response => {
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement('a');
         
              fileLink.href = fileURL;
              fileLink.setAttribute('download', 'export.'+format);
              document.body.appendChild(fileLink);
         
              fileLink.click();
          })
            .catch(error => {
              console.log(error);
          }); */
    }        
}

</script>
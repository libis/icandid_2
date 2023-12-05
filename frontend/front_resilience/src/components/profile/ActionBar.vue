<template>
    <div>
        <div class="columns">
            <div class="column">
            </div>
            
            <div class="column has-text-right">
                <button class="button is-rounded" :title="$ml.get('exportsetcsv')" @click="exportSet('csv')"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i>.csv</button>
                <button class="button is-rounded" :title="$ml.get('exportsetxlsx')" @click="exportSet('xlsx')"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i>.xslx</button>
                <!-- <button class="button is-rounded" :title="$ml.get('exportsettxt')" @click="exportSet('txt')"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i>.txt</button> -->
                <button class="button is-rounded" :title="$ml.get('exportsetjson')" @click="exportSet('json')"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i>.json-ld</button>
            </div>
        </div>
        <Dialog ref="dialog"></Dialog>
    </div>
</template>
<script>
import axios from 'axios';
import { mapGetters } from 'vuex';
import Dialog from '../helpers/Dialog.vue'
export default {
    computed: mapGetters(['getHits','getSearchRequest','getApiProfileUrl']),
    components: {
        Dialog
    },
    props:['shelfId'],
    methods: {
        exportSet(format) {
          var url = this.getApiProfileUrl + '/queue'
          var request = {shelfid:this.shelfId, format:format, language: this.$ml.current }
          axios
            .post(url, request)
            .then(response => {
                this.$refs.dialog.alert(this.$ml.get('info'),this.$ml.with('email', response.data.email).get('exportinprogress'))
          })
            /*.catch(error => {
              console.log(error);
          })*/;            
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
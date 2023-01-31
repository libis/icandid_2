<template>
    <div class="block " v-if="showhomepage">
        <!--
            <article class="message is-warning" v-if="!getAuthenticated && getAuthenticated != undefined">
            <div class="message-header">
                <p>{{ $ml.get('accessdenied') }}</p>
            </div>
            <div class="message-body">
                {{ $ml.get('youhavenoaccess')}} 
            </div>
            </article>
        -->
        <h2 class="title is-2">{{ $ml.get('home_title') }}</h2>
        <div class="columns">
            <div class="column is-half">
                               
                <div class="box">
                    <p><Content :lang="$ml.current" :contentcode="'home_intro'" :show_title="false"></Content></p>
                    <p><Content :lang="$ml.current" :contentcode="'home_limited'" :show_title="false" ></Content></p>
                    <p v-if="!getAuthenticated"><Content :lang="$ml.current" :contentcode="'home_notloggedin'" :show_title="false"></Content></p>
                    <p><Content :lang="$ml.current" :contentcode="'home_moreinfo'" :show_title="false"></Content></p>

                </div>
            </div>
            <div class="column is-half">
                <!-- <img style="transform: rotate(-5deg);padding-top: 25px" src="https://upload.wikimedia.org/wikipedia/commons/0/09/YouTube_full-color_icon_%282017%29.svg" width="100%"> -->
                <iframe width="560" height="315" src="https://www.youtube.com/embed/g6jt9FADjUc" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

            </div>
        </div>
    </div>
</template>
<script>
import Content from './helpers/Content.vue'
import { mapGetters } from "vuex";
export default {
    components: {
        Content
    },
    data() {
        return {
            showhomepage:false,
        }
    },
    computed: {
      ...mapGetters(["getAuthenticated","getUsername","getPermissions","getApiStatusUrl"]),
    },
    methods: {
        redir() {
/*            if (this.getPermissions.indexOf("search_collections") >= 0) {
                this.$router.push('/search/collection')  
            } else if (this.getPermissions.indexOf("search_media") >= 0) {
                this.$router.push('/search/media')   */

            //console.log(this.$cookies.get('wantsaccess'))
            if (this.$cookies.get('wantsaccess')) {
                //console.log('redirect /request/access')
                this.$router.push('/request/access')
            }  else { 
                if (this.getPermissions.indexOf("search") >= 0) {
                    //console.log('redirect /search')
                    this.$router.push('/search')
                } else {
                    this.showhomepage=true
                }
            }            
        }
    },
    created() {
        if (this.getAuthenticated) {
          this.redir()
        } 
    },
    watch:{
        getAuthenticated: function() {
            if (this.getAuthenticated) {
                this.redir()
            }            
        }
    }

}
</script>
<style scoped>
p { padding-bottom:16px}
</style>
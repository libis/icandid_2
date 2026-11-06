<template>
    <div class="block" style="margin-bottom:0px">
        <div class="columns">
            <div class="column is-12">
                <p>
                    <Content :lang="$ml.current" :contentcode="'explore_intro'" :show_title=true></Content>
                </p>
            </div>
            <!-- <div class="column is-half" style="display: grid; align-items: center; justify-items: center;">
                <div style="display:flex;gap:40px">
                    <img src="https://assets.libis.be/reiresearch/eu_funded_logo.png" style="height:48px">
                    <img src="https://assets.libis.be/reiresearch/resilience_logo.png" style="height:48px">
                </div>
                <div>
                    <img src="https://assets.libis.be/libis/libisnewkuljr.png" style="height:48px">
                </div>
            </div> -->

        </div>
        <div class="columns">
            <div class="column is-11">
                <input class="input input_intro" type="text" v-model="searchterm"
                    :placeholder="$ml.get('search_into') + '...'" @keyup.enter="search()">
            </div>
            <div class="column is-1">
                <button class="button is-primary" style="background-color: #0625AC; border-color: #0625AC;"
                    @click="search()">{{ $ml.get('search') }}</button>
            </div>

        </div>
        <div v-if="getHistory.length > 0 && (this.getPermissions.indexOf('save')>-1)">
            <div style="display:flex;margin-top:30px;width:100%">
                <div style="width:50%"><h2 class="title is-5">{{ $ml.get('latestsearches') }}</h2></div>
                <div style="width:50%;display:flex;justify-content:flex-end">
                    <div class="histbutton" @click="$router.push('/profile/history')"><Icon icon="historyclock" width="14" color="#202F3E"></Icon>{{ $ml.get('seeall') }}</div>
                </div>
            </div>
            <HistoryList :count="3"></HistoryList>
        </div>
        <div class="collections">
            <CollectionsPanel :count="6" :gridwidth="3" />
            <a class="button is-primary" style="background-color: #0625AC; border-color: #0625AC;font-size:18px;margin-top:20px" href="/#/collections">{{ $ml.get('moreaboutcollections') }}&nbsp;&nbsp;&nbsp;<i class="fa fa-arrow-right" aria-hidden="true"></i></a>

        </div>
        <div class="doctypes">
            <h3 class="title is-2">{{ $ml.get('doctypes_title') }}</h3>
            <div class="container">
                <div class="plaque" @click="searchFilter('type','Book')">
                    <div style="background: #E5EEFF;" class="plaque_label">
                        <Icon color="#0625AC" width="65" icon="book"></Icon>
                    </div>
                    <div>
                        <h3>{{ $ml.get('book')}}</h3>
                        <p>{{ $ml.get('book_text') }}</p>
                    </div>
                </div>
                <div class="plaque" @click="searchFilter('type','Manuscript')">
                    <div style="background: #EDF9FF; " class="plaque_label">
                        <Icon color="#0579B8" width="63" icon="manuscript"></Icon>
                    </div>
                    <div>
                        <h3>{{ $ml.get('manuscript') }}</h3>
                        <p>{{ $ml.get('manuscript_text')}}</p>
                    </div>
                </div>
                <div class="plaque" @click="searchFilter('type','ArchiveComponent')">
                    <div style="background: #FFF4E3; " class="plaque_label">
                        <Icon color="#663D00" width="70" icon="archive"></Icon>
                    </div>
                    <div>
                        <h3>{{ $ml.get('archives')}}</h3>
                        <p>{{ $ml.get('archives_text') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="about">
            <div class="columns">
                <div class="column is-half">
                    <p>
                        <Content :lang="$ml.current" :contentcode="'about_intro'" :show_title=true></Content>
                    </p>
                </div>
                <div class="column is-half">
                    <img src="https://assets.libis.be/reiresearch/intro_about.png" alt="About Image"
                        style="width:100%; ">
                </div>
            </div>
        </div>
        <div class="updates" v-if="contentcodes.length > 0">
            <h3 class="title is-2">{{ $ml.get('updates_title') }}</h3>
            <div v-for="contentcode in contentcodes" :key="contentcode" class="update-item">
                <div class="update-label"><img src="/img/updates_logo.png" alt="Update Icon"></div>
                <div class="update-content">
                    <Content :lang="$ml.current" :contentcode="contentcode" :show_title=true titleclass="title is-4">
                    </Content>
                </div>
            </div>


        </div>
    </div>
</template>
<script>
import axios from "axios";
axios.defaults.withCredentials = true;
import CollectionsPanel from './helpers/CollectionsPanel.vue';
import Content from './helpers/Content.vue';
import Icon from './helpers/Icon.vue';
import { mapActions, mapGetters } from 'vuex';
import HistoryList from "./profile/HistoryList.vue";
export default {
    components: {
        Content,
        CollectionsPanel,
        Icon,
        HistoryList
    },
    data() {
        return {
            showhomepage: false,
            contentcodes: [],
            searchterm: ""
        }
    },
    computed: {
        ...mapGetters(["getAuthenticated", "getUsername", "getPermissions", "getApiStatusUrl", 'getApiContentUrl','getHistory','getUser']),
    },
    methods: {
        ...mapActions(['setQuery', 'resetQuery','setSelectedFilters', 'clearFilters', 'getSearchRequest']),
        search() {
            if (this.searchterm.length > 2) {
                this.setQuery({ searchtype: "simple", q: this.searchterm, f: [] })
                this.$router.push("/search")
            }
        },
        searchFilter(filterName, value) {
            var newFilters = {
                type:[],
                collection:[],
                inlanguage:[],
                datePublished_from:"",
                datePublished_until:"",
                author:[],
                contributor:[],
                publisher:[],
                locationcreated:[]
            }
            this.$router.push("/search")
            //this.setQuery({ searchtype: "simple", q: "", f: []})
            this.resetQuery({ searchtype: "simple", q: "", f: [], sf: newFilters})
            window.setTimeout(function() {
                newFilters[filterName] = [value]
                this.setSelectedFilters(newFilters)
            }.bind(this), 500)
        }
    }, 
    created() {
        axios.get(this.getApiContentUrl + "/updates_*")
            .then(res => {
                this.contentcodes = res.data;
            });
    },
    watch: {
    }

}
</script>
<style scoped>
p {
    padding-bottom: 16px
}

.doctypes {
    background: #fafafa;
    padding: 50px
}

.container {
    display: grid;
    grid-template-columns: auto auto auto;
    gap: 16px;
    /* spacing between images */

}

.plaque {
    display: grid;
    grid-template-columns: 50% 50%;
    background: white;
    border-radius: 10px;
    cursor:pointer;
}

.plaque div {
    padding: 10px
}

.plaque div.plaque_label {
    display: grid;
    align-items: center;
    justify-items: center;
}

.plaque div.plaque_label img {
    width: 90px;
}

.plaque p {

    margin-top: 0px;
    font-size: 0.9em;
    font-style: italic;
}

.plaque h3 {
    margin-top: 0px;
    font-size: 1.2em;
    font-weight: bold;
}

.about {
    margin-top: 50px;
    margin-bottom: 50px;
    margin-left: 50px;
    margin-right: 50px;
}

.updates {
    background: #FFF4E3;
    padding: 50px;
}

.updates h3 {
    color: #663D00
}

.updates .update-item {
    border-radius: 10px;
    display: grid;
    grid-template-columns: 150px auto;
    margin-bottom: 16px;
    box-shadow: 1px 1px 5px #ccc;
}

.collections {
    margin-top: 50px;
    margin-bottom: 50px;
}

.updates .update-content {
    background: white;
    border-radius: 0px 10px 10px 0px;
    padding: 10px;
}

.updates .update-label {
    background: #EBEFF3;
    border-radius: 10px 0px 0px 10px;
    display: grid;
    align-items: center;
    justify-items: center;
}

.updates .update-label img {
    background: #EBEFF3;
}

.input_intro {
    border: 1px;
    border-color: #809AB0;
    border-radius: 5px;
    box-shadow: 1px 1px 3cqb #ccc;
}

.input_intro:focus {
    border: 1px;
    border-color: #809AB0;
}
.histbutton {
    padding-top: 8px;
    padding-right: 8px;
    padding-bottom: 8px;
    padding-left: 6px;
    border-radius: 6px;
    background-color:#EBEFF3;
    cursor:pointer;
    color:#202F3E;
    text-decoration:none;
    font-weight: 600;
    font-size: 12px;
    line-height: 16px;
    letter-spacing: 0%;
    text-align: center;
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}
</style>
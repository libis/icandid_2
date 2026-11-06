<template>
    <div>
        <h1 class="title">{{ $ml.get('contactus') }}</h1>
        <div class="columns" style="margin-bottom:120px">
            <div class="column is-half">
                <div style="display:flex">
                    <div class="iconbox">
                        <Icon icon="quill" width="50" color="#0579B8"></Icon>
                    </div>
                    <Content :lang="$ml.current" contentcode="contact" key="contact" :show_title=false style="padding-bottom:20px"></Content>
                </div>
            </div>
            <div class="column is-half" style="padding-bottom:40px">
<!--                <div class="contactbox" v-if="formopen">
                    <h1>{{ $ml.get('contactformtitle') }}</h1>
                    <span>{{ $ml.get('contactfieldname') }}</span>
                    <input type="text" v-model="form.name">
                    <span>{{ $ml.get('contactfieldemail') }}</span>
                    <input type="text" v-model="form.email">
                    <div>
                        <span>{{ $ml.get('contactoptions') }}</span>
                        <label>
                            <input type="radio" name="option" :value="$ml.get('contactoption1')" v-model="form.option">
                            {{ $ml.get('contactoption1') }}
                        </label>

                        <label>
                            <input type="radio" name="option" :value="$ml.get('contactoption2')" v-model="form.option">
                            {{ $ml.get('contactoption2') }}
                        </label>

                        <label>
                            <input type="radio" name="option" :value="$ml.get('contactoption3')" v-model="form.option">
                            {{ $ml.get('contactoption3') }}
                        </label>
                    </div>
                    <span>{{ $ml.get('contactfieldmessage') }}</span>
                    <textarea v-model="form.message"></textarea>
                    <label><input type="checkbox" style="width:auto;margin-right:10px" v-model="form.tos"><span
                            v-html="$ml.get('contacttos')"></span></label>
                    <div class="contactbutton" v-bind:class="{ ok: isOk }" @click="send">
                        {{ $ml.get('contactsend') }}
                    </div>
                </div>
                <div v-if="resultopen" class="result">{{ $ml.get("contactmessageok") }}</div>
                <div v-if="erroropen" class="error" v-html="$ml.get('contactmessageerror')"></div>   -->
            </div> 
        </div>
    </div>
</template>
<script>
import Icon from './helpers/Icon.vue'
import Content from './helpers/Content.vue'
import axios from 'axios';
import { mapGetters } from 'vuex';
export default {
    name: "Contact",
    props: {},
    data() {
        return {
            form: {
                name: "",
                email: "",
                message: "",
                option: "",
                tos: false
            },
            formopen:true,
            resultopen:false,
            erroropen:false,
        }
    },
    components: {
        Icon,
        Content
    },
    methods: {
        send() {
            if (this.isOk) {
                this.formopen = false
                var data = { "subject": "Contactform", "formdata": this.form }
                axios
                    .post(this.getApiFormUrl + '/contact', data)
                    .then(() => {
                        this.resultopen = true
                    }
                    )
                    .catch(() => {
                        this.erroropen = true
                    }
                    );
            }
        }
    },
    computed: {
        ...mapGetters(['getApiFormUrl']),
        isOk() {
            return (this.form.name != "" && this.form.email != "" && this.form.message != "" && this.form.option != "" && this.form.tos)
        }
    }
}
</script>
<style scoped>

.iconbox {
    background-color: #EDF9FF;
    min-width: 100px;
    max-height: 110px;
    min-height:110px;
    margin-right: 15px;
    vertical-align: middle;
    text-align: center;
    padding-top: 25px
}

.contactbox .contactbutton {
    width: 100;
    height: 48;
    gap: 4px;
    padding-top: 16px;
    padding-right: 12px;
    padding-bottom: 16px;
    padding-left: 12px;
    border-radius: 6px;
    color: #A3A3A3;
    font-weight: 700;
    font-size: 15px;
    line-height: 20px;
    letter-spacing: 0%;
    text-align: center;
    background-color: #E5E5E5;
}

.contactbox .contactbutton.ok {
    color: #FFFFFF;
    background-color: #0625AC;
    cursor: pointer
}


.contactbox {
    background-color: #EDF9FF;
    border-radius: 5px;
    padding: 10px
}

.contactbox h1 {
    font-weight: 700;
    font-size: 18px;
    line-height: 24px;
    letter-spacing: 0px;
    color: #202F3E;
    margin-bottom: 5px
}

.contactbox span {
    margin-top: 10px;
    color: #455B71;
    font-weight: 600;
    font-size: 13px;
    line-height: 15px;
    letter-spacing: 0px;
    vertical-align: middle;
}

.contactbox input {
    width: 100%;
    height: 40;
    gap: 10px;
    padding-top: 4px;
    padding-right: 8px;
    padding-bottom: 4px;
    padding-left: 8px;
    border-radius: 6px;
    border-width: 1px;
}

.contactbox textarea {
    width: 100%;
    height: 200px;
    opacity: 1;
    border-radius: 6px;
    border-width: 1px;
    padding: 8px;
}

.contactbox div {
    display: grid;
    color: #1A2130;
    width: 100%;
    border-radius: 8px;
    padding-left: 16px;
    padding-right: 16px;
    background-color: #FFFFFF;
    margin-top: 10px;
    padding-bottom: 10px;
}

.contactbox label {
    font-weight: 600;
    font-size: 14px;
    line-height: 15px;
    letter-spacing: 0%;
    margin-top: 10px;
}

.contactbox label input {
    width: 20px
}
.result {
    background-color:#D5FCF1;
    border-radius:6px;
    text-align: center;
    padding:20px
    
}
.error {
    background-color: #f75617;
    border-radius:6px;
    text-align: center;
    padding:20px;
}
</style>
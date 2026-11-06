<template>
  <div class="social-share">
    <!-- LinkedIn -->
    <ShareNetwork
      network="linkedin"
      :url="url"
      :title="title"
      :description="description"
      @open="open"
    >
      <button class="share-btn linkedin">
        <LinkedInIcon />
        <span>LinkedIn</span>
      </button>
    </ShareNetwork>

    <!-- Facebook -->
    <ShareNetwork
      network="facebook"
      :url="url"
      :title="title"
      :description="description"
      @open="open"
    >
      <button class="share-btn facebook">
        <FacebookIcon />
        <span>Facebook</span>
      </button>
    </ShareNetwork>

    <!-- X / Twitter -->
    <ShareNetwork
      network="twitter"
      :url="url"
      :title="title"
      hashtags="vuejs,javascript"
      @open="open"
    >
      <button class="share-btn twitter">
        <XIcon />
        <span>X</span>
      </button>
    </ShareNetwork>

    <!-- Email -->
    <ShareNetwork
      network="email"
      :url="url"
      :title="title"
      :description="description"
      @open="open"
    >
      <button class="share-btn email">
        <EmailIcon />
        <span>Email</span>
      </button>
    </ShareNetwork>
  </div>
</template>

<script>
import LinkedInIcon from '../icons/LinkedIn.vue'
import FacebookIcon from '../icons/Facebook.vue'
import XIcon from '../icons/X.vue'
import EmailIcon from '../icons/Email.vue'

export default {
  props: {
    url: String,
    title: String,
    description: String
  },

  components: {
    LinkedInIcon,
    FacebookIcon,
    XIcon,
    EmailIcon
  },
  methods:{
    open() {
        this.$parent.showShare = false;
    }
  }
}
</script>

<style scoped>
.social-share a,
.social-share a:visited,
.social-share a:hover,
.social-share a:active {
  text-decoration: none;
}


.social-share {
  display: flex;
  gap: 12px;
  padding:8px
}

.share-btn {
  display: flex;
  align-items: center;
  gap: 8px;

  border: none;
  border-radius: 8px;
  padding: 10px 16px;

  color: white;
  font-weight: 600;
  cursor: pointer;
}

.share-btn svg {
  width: 18px;
  height: 18px;
}

.linkedin {
  background: #0a66c2;
}

.facebook {
  background: #1877f2;
}

.twitter {
  background: #000;
}

.email {
  background: #666;
}
</style>
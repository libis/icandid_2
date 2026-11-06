<template>
  <transition name="slide-up">
    <div v-if="!accepted" class="terms-banner">
      <div class="terms-content">
        <div class="terms-text">
          By using this website, you agree to our
          <a href="/#/page/tos" target="_blank">
            Terms & Conditions
          </a>.
        </div>

        <div class="terms-actions">
          <label class="checkbox-label">
            <input
              type="checkbox"
              v-model="isChecked"
            />
            I accept the Terms & Conditions
          </label>

          <button
            :disabled="!isChecked"
            @click="acceptTerms"
            class="accept-btn"
          >
            Continue
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import Cookies from 'js-cookie';

const COOKIE_NAME = 'terms_accepted';
const COOKIE_EXPIRY_DAYS = 365;

export default {
  name: 'TermsConsent',

  data() {
    return {
      accepted: false,
      isChecked: false
    };
  },

  mounted() {
    this.accepted = Cookies.get(COOKIE_NAME) === 'true';
  },

  methods: {
    acceptTerms() {
      if (!this.isChecked) {
        return;
      }

      Cookies.set(COOKIE_NAME, 'true', {
        expires: COOKIE_EXPIRY_DAYS,
        sameSite: 'Lax',
        secure: window.location.protocol === 'https:'
      });

      this.accepted = true;

      this.$emit('accepted');
    }
  }
};
</script>

<style scoped>
.terms-banner {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 9999;
  background: #0625AC;
  color: white;
  padding: 16px;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.2);
  font-size:0.8em;
}

.terms-content {
  max-width: 1200px;
  margin: 0 auto;

  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;

  flex-wrap: wrap;
}

.terms-text a {
  color: white;
}

.terms-actions {
  display: flex;
  align-items: center;
  gap: 15px;
}

.accept-btn {
  background: white;
  color: black;
  border: none;
  border-radius: 4px;
  padding: 8px 16px;
  cursor: pointer;
}

.accept-btn:disabled {
  opacity: 0.5; 
  background:grey;
  cursor: not-allowed;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter,
.slide-up-leave-to {
  transform: translateY(100%);
}
</style>
<template>
  <section>
    <v-container grid-list-md>
      <v-layout align-center>
        <v-flex class="text-center" xs12>
          <h2 class="section-title mb-5">{{ $t('get_in_touch') }}</h2>
          <v-layout row wrap justify-space-around class="text-left">
            <v-flex xs12 md6>
              <contact-form/>
            </v-flex>
            <v-flex xs12 md4>
              <v-hover>
                <v-card slot-scope="{ hover }" :class="`elevation-${hover ? 12 : 1}`">
                  <div class="contact-block">
                    <h4 class="headline">{{ $t('contact_information') }}</h4>
                    <div class="flex pb-6">
                      <div class="con-text" v-if="settings.contact_email">
                        <v-icon small>mdi-email-outline</v-icon>
                        <a :href="`mailto:${settings.contact_email}`">{{ settings.contact_email }}</a>
                      </div>
                    </div>
                    <div class="social mt-4">
                      <v-btn v-if="settings.facebook" color="#3b5998" dark small fab :href="settings.facebook" target="_blank">
                        <v-icon>mdi-facebook</v-icon>
                      </v-btn>
                      <v-btn v-if="settings.twitter" color="#55acee" dark small fab :href="settings.twitter" target="_blank">
                        <v-icon>mdi-twitter</v-icon>
                      </v-btn>
                      <v-btn v-if="settings.linkedin" color="#1985bc" dark small fab :href="settings.linkedin" target="_blank">
                        <v-icon>mdi-linkedin</v-icon>
                      </v-btn>
                      <v-btn v-if="settings.github" color="#222" dark small fab :href="settings.github" target="_blank">
                        <v-icon>mdi-github</v-icon>
                      </v-btn>
                      <v-btn v-if="settings.gitlab" color="#fca326" dark small fab :href="settings.gitlab" target="_blank">
                        <v-icon>mdi-gitlab</v-icon>
                      </v-btn>
                      <v-btn v-if="settings.bitbucket" color="#205081" dark small fab :href="settings.bitbucket" target="_blank">
                        <v-icon>mdi-bitbucket</v-icon>
                      </v-btn>
                    </div>
                  </div>
                </v-card>
              </v-hover>
            </v-flex>
          </v-layout>
        </v-flex>
      </v-layout>
    </v-container>
  </section>
</template>

<script>
import ContactForm from '~/components/ContactForm'
import { mapGetters } from 'vuex'

export default {
  name: 'contact',
  components: {
    ContactForm
  },
  head() {
    return {
      title: this.$t('contact')
    }
  },
  computed: {
    ...mapGetters({
      settings: 'settings/settings'
    })
  }
}
</script>

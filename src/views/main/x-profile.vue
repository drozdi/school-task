<template>
  <v-card class="mx-auto">
    <v-card-text>
      <v-form ref="form">
        <input type="hidden" name="user[id]" :value="data.id" />
        <v-row>
          <v-col md="6">
            <v-text-field name="user[email]" v-model="data.email" outlined label="Email" placeholder="Email" :type="'email'" :rules="rules.email"></v-text-field>
            <v-text-field name="user[alias]" v-model="data.alias" outlined label="Обращение" placeholder="Обращение" :rules="rules.alias"></v-text-field>
            <v-text-field name="user[second_name]" v-model="data.second_name" outlined label="Фамилия" placeholder="Фамилия" :rules="rules.patronymic"></v-text-field>
            <v-text-field name="user[first_name]" v-model="data.first_name" outlined label="Имя" placeholder="Имя" :rules="rules.patronymic"></v-text-field>
            <v-text-field name="user[patronymic]" v-model="data.patronymic" outlined label="Отчество" placeholder="Отчество" :rules="rules.patronymic"></v-text-field>
          </v-col>
          <v-col md="6">
            <v-row>
              <v-col sm="4">
                Дата регистрации:
              </v-col>
              <v-col sm="8">
                {{data.date_register}}
              </v-col>
            </v-row>
            <v-row v-if="data.tutor">
              <v-col sm="4">
                Куратор:
              </v-col>
              <v-col sm="8">
                {{data.tutor}}
              </v-col>
            </v-row>
            <v-row>
              <v-col sm="4">
                Время последнего входа:
              </v-col>
              <v-col sm="8">
                {{data.last_login}}
              </v-col>
            </v-row>
            <v-row>
              <v-col sm="4">
                Время изменения:
              </v-col>
              <v-col sm="8">
                {{data.x_timestamp}}
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-form>
    </v-card-text>
    <v-card-actions>
      <v-btn color="green" @click="submit">
        Сохранить
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  data: () => ({
    data: {},
    rules: {
      alias: [
        v => !!v || 'Alias is required',
        v => (v && v.length >= 6) || 'Обращение должен быть больше 6 симоволов',
      ],
      email: [
        v => !!v || 'Alias is required',
      ],
      first_name: [
        v => !!v || 'first_name is required',
      ],
      second_name: [
        v => !!v || 'second_name is required',
      ],
      patronymic: [
        v => !!v || 'patronymic is required',
      ],
    }
  }),
  methods: {
    load () {
      let formData = new FormData();
      formData.append('f', "load");
      return this.$http.post('/main/profile.php', formData).then((function (res) {
        this.data = res;
      }).bind(this));
    },
    submit () {
      let formData = new FormData(this.$refs.form.$el);
      formData.append('f', "save");
      return this.$http.post('/main/profile.php', formData).then((function (res) {
        this.data = res;
      }).bind(this));
    }
  },
  mounted() {
    this.load();
  }
}
</script>

<style scoped>

</style>
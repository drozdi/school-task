<template>
  <v-form ref="form">
    <v-card class="mx-auto">
      <v-card-title class="text-h5">
        {{ title }} <v-spacer></v-spacer>{{ xTimestamp }}
      </v-card-title>
      <v-card-text>
        <v-tabs background-color="primary" dark>
          <v-tab>
            Общее
          </v-tab>
          <v-tab>
            Группы
          </v-tab>
          <v-tab>
            Права
          </v-tab>
          <v-tab-item>
            <v-card flat>
              <v-card-text>
                <v-text-field name="user[login]" v-model="data.login" outlined label="Логин" placeholder="Логин" :rules="rules.login" required></v-text-field>
                <v-select name="user[ou_id]" v-model="data.ou_id" outlined :items="ous" label="Подразделение"></v-select>
                <v-select name="user[parent_id]" v-model="data.parent_id" outlined :items="tutors" label="Куратор"></v-select>
                <v-text-field name="user[alias]" v-model="data.alias" outlined label="Псевдоним" placeholder="Псевдоним" :rules="rules.alias"></v-text-field>
                <v-checkbox name="user[active]" v-model="data.active" label="Активная"></v-checkbox>
                <v-checkbox name="user[loocked]" v-model="data.loocked" label="Заблокирован"></v-checkbox>
                <v-datetime-picker
                    :text-field-props="textFieldProps('user[activeFrom]')"
                    :time-picker-props="timePickerProps"
                    timeFormat="HH:mm:ss"
                    v-model="data.activeFrom"
                    label="Активный с"
                    placeholder="Активный c"
                > </v-datetime-picker>
                <v-datetime-picker
                    :text-field-props="textFieldProps('user[activeTo]')"
                    :time-picker-props="timePickerProps"
                    timeFormat="HH:mm:ss"
                    v-model="data.activeTo"
                    label="Активный по"
                    placeholder="Активный по"
                > </v-datetime-picker>
                <v-text-field name="user[email]" v-model="data.email" outlined label="Email" placeholder="Email" :type="'email'" :rules="rules.email"></v-text-field>
                <v-text-field name="user[phone]" v-model="data.phone" outlined label="Псевдоним" placeholder="Телефон" :rules="rules.phone"></v-text-field>
                <v-text-field name="user[first_name]" v-model="data.first_name" outlined label="Имя" placeholder="Имя" :rules="rules.patronymic"></v-text-field>
                <v-text-field name="user[second_name]" v-model="data.second_name" outlined label="Фамилия" placeholder="Фамилия" :rules="rules.patronymic"></v-text-field>
                <v-text-field name="user[patronymic]" v-model="data.patronymic" outlined label="Отчество" placeholder="Отчество" :rules="rules.patronymic"></v-text-field>
                <v-text-field name="user[gender]" v-model="data.gender" outlined label="Пол" placeholder="Пол"></v-text-field>
                <v-text-field name="user[country]" v-model="data.country" outlined label="Страна" placeholder="Страна"></v-text-field>
                <v-textarea name="user[description]" v-model="data.description" outlined label="Описание" placeholder="Описание" :rules="rules.description"></v-textarea>
              </v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item>
            <v-card flat>
              <v-card-text>
                <v-select :items="groups" label="Добавить" @change="addGroup" ref="groups"></v-select>
                <v-simple-table>
                  <template v-slot:default>
                    <tbody>
                    <tr v-for="(item, index) in data.groups" :key="index">
                      <td>
                        {{item.name}}
                        <input type="hidden" :name="'user[groups]['+index+'][group_id]'" :value="item.group_id">
                        <input type="hidden" :name="'user[groups]['+index+'][user_id]'" :value="item.user_id">
                      </td>
                      <td>
                        <v-datetime-picker
                            :text-field-props="textFieldProps('user[groups]['+index+'][activeFrom]')"
                            :time-picker-props="timePickerProps"
                            timeFormat="HH:mm:ss"
                            v-model="item.activeFrom"
                            label="Активный с"
                            placeholder="Активный c"
                        > </v-datetime-picker>
                      </td>
                      <td>
                        <v-datetime-picker
                            :text-field-props="textFieldProps('user[groups]['+index+'][activeTo]')"
                            :time-picker-props="timePickerProps"
                            timeFormat="HH:mm:ss"
                            v-model="item.activeTo"
                            label="Активный по"
                            placeholder="Активный по"
                        > </v-datetime-picker>
                      </td>
                      <td>
                        <v-btn small color="warning" title="Удалить" @click="delGroup(index)">
                          <v-icon>
                            mdi-delete
                          </v-icon>
                        </v-btn>
                      </td>
                    </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item>
            <v-card flat>
              <v-card-text>
                <v-select :items="claimants" label="Добавить" @change="addAccess" ref="claimants"></v-select>
                <v-simple-table>
                  <template v-slot:default>
                    <tbody>
                    <tr v-for="(item, index) in data.accesses" :key="index">
                      <td>
                        {{item.name}}
                        <input type="hidden" :name="'user[accesses]['+index+'][user_id]'" :value="item.user_id">
                        <input type="hidden" :name="'user[accesses]['+index+'][claimant_id]'" :value="item.claimant_id">
                      </td>
                      <td>
                        <v-text-field :name="'user[accesses]['+index+'][access]'" v-model="item.access" :type="'number'" label="Level"></v-text-field>
                      </td>
                    </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </v-card-text>
            </v-card>
          </v-tab-item>
        </v-tabs>
      </v-card-text>
      <v-card-actions>
        <v-btn class="mr-4" color="green" @click="save(true)">
          Сохранить
        </v-btn>
        <v-btn class="mr-4" color="primary" @click="save(false)">
          Применить
        </v-btn>
        <v-btn @click="$router.back();">
          Назад
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-form>
</template>

<script>
let ii = 0;
export default {
  name: "MainUser",
  data: () => ({
    snackbar: false,
    snackbar_text: '',
    rules: {
      login: [
        v => !!v || 'Login is required',
        v => (v && v.length <= 255) || 'Логин должен быть меньше 255 символов',
      ],
      alias: [
        v => !!v || 'Alias is required',
        v => (v && v.length >= 6) || 'Псевдоним должен быть больше 6 симоволов',
      ],
      email: [],
      first_name: [],
      second_name: [],
      patronymic: [],
      description: []
    },
    oldName: null,
    ous: [],
    tutors: [],
    groups: [],
    claimants: [],
    data: {
      id: null,
      parent_id: null,
      x_timestamp: "",
      date_register : "",
      last_login : "",
      last_ip: "",
      active: true,
      activeFrom: null,
      activeTo: null,
      loocked : false,
      stored_hash: "",
      checkword: "",
      login: "",
      email: "",
      password: "",
      alias: "",
      first_name: "",
      second_name: "",
      patronymic: "",
      gender: "",
      login_attempts: 0,
      country: "RU",
      ou_id: null,
      phone: null,
      description: null
    },
    timePickerProps: {
      ampmInTitle: true,
      format: "24hr"
    },
  }),
  computed: {
    title () {
      if (this.data.id) {
        return 'Пользователь "' + this.oldName + '"';
      } else {
        return "Новый пользователь";
      }
    },
    xTimestamp () {
      if (this.data.id) {
        const t = new Date(this.data.x_timestamp)
        return t.toLocaleString()
      }
      return "";
    }
  },
  methods: {
    textFieldProps (name) {
      name = name || ""
      return {
        name: name,
        type: 'datetime'
      }
    },
    save (back) {
      const isNew = !this.data.id;
      const formData = new FormData(this.$refs.form.$el);
      formData.append('f', "save");
      formData.append('user[active]', this.data.active? 1: 0);
      formData.append('user[loocked]', this.data.anonymous? 1: 0);
      if (!isNew) {
        formData.append('user[id]', this.data.id);
      }
      return this.$http.post('/main/user.php', formData).then((function (data) {
        if (isNew) {
          this.$store.dispatch('snackbar/message', 'Пользователь "' + data.login + ' (' + data.alias + ')", успешно создан!');
        } else {
          this.$store.dispatch('snackbar/message', 'Пользователь "' + data.login + ' (' + data.alias + ')", успешно изменен!');
        }
        this.oldName = this.data.login;
        if (back) {
          this.$router.go(-1);
        } else {
          this.data = data;
          if (!this.data.accesses) {
            this.$set(this.data, 'accesses', {})
          }
          if (!this.data.groups) {
            this.$set(this.data, 'groups', {})
          }
        }
      }).bind(this));
    },
    load () {
      const formData = new FormData();
      formData.append('f', "load");
      formData.append('user[login]', this.$route.params.login);
      return this.$http.post('/main/user.php', formData).then((function (data) {
        this.data = data;
        if (!this.data.accesses) {
          this.$set(this.data, 'accesses', {})
        }
        if (!this.data.groups) {
          this.$set(this.data, 'groups', {})
        }
        this.oldName = this.data.login;
      }).bind(this));
    },
    loadOUs () {
      const formData = new FormData();
      formData.append('f', "load-ous");
      return this.$http.post('/main/user.php', formData).then((function (data) {
        this.ous = data;
      }).bind(this));
    },
    loadTutors () {
      const formData = new FormData();
      formData.append('f', "load-tutors");
      formData.append('id', this.data.id);
      return this.$http.post('/main/user.php', formData).then((function (data) {
        this.tutors = data;
      }).bind(this));
    },
    loadClaimants () {
      const formData = new FormData();
      formData.append('f', "load-claimants");
      return this.$http.post('/main/user.php', formData).then((function (data) {
        this.claimants = data;
      }).bind(this));
    },
    loadGroups () {
      const formData = new FormData();
      formData.append('f', "load-groups");
      return this.$http.post('/main/user.php', formData).then((function (data) {
        this.groups = data;
      }).bind(this));
    },
    addAccess (val) {
      let notAppend = false;
      let addItem = {
        id: 'n'+(ii++),
        user_id: this.data.id,
        claimant_id: val,
        name: "",
        access: 0
      };
      this.claimants.forEach(function (item) {
        if (item.value == val) {
          addItem.name = item.text;
        }
      });
      Object.keys(this.data.accesses).forEach((function (key) {
        notAppend = notAppend || this.data.accesses[key].claimant_id == val;
      }).bind(this));
      if (notAppend) {
        this.$store.dispatch('snackbar/message', 'Нельзя добавить "' + addItem.name + '"! Уже есть!');
      } else {
        this.$set(this.data.accesses, addItem.id, addItem)
      }
      this.$refs.claimants.reset();
    },
    addGroup  (val) {
      let notAppend = false;
      let addItem = {
        id: 'n'+(ii++),
        group_id: val,
        user_id: this.data.id,
        name: "",
        activeFrom: null,
        activeTo: null,
      };
      this.groups.forEach(function (item) {
        if (item.value == val) {
          addItem.name = item.text;
        }
      });
      Object.keys(this.data.groups).forEach((function (key) {
        notAppend = notAppend || this.data.groups[key].group_id == val;
      }).bind(this));
      if (notAppend) {
        this.$store.dispatch('snackbar/message', 'Нельзя добавить "' + addItem.name + '"! Уже есть!');
      } else {
        this.$set(this.data.groups, addItem.id, addItem)
      }
      this.$refs.groups.reset();
    },
    delGroup (val) {
      this.$delete(this.data.groups, val);
    }
  },
  mounted () {
    if (this.$route.params.ou_id > 0) {
      this.data.ou_id = this.$route.params.ou_id;
    }
    this.load()
        .finally((function () {
          return this.loadOUs();
        }).bind(this))
        .finally((function () {
          return this.loadTutors();
        }).bind(this))
        .finally((function () {
          return this.loadGroups();
        }).bind(this))
        .finally((function () {
          return this.loadClaimants();
        }).bind(this));

  }
}
</script>

<style scoped>

</style>
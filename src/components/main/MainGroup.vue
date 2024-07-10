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
            Пользователи
          </v-tab>
          <v-tab>
            Права
          </v-tab>
            <v-tab-item>
              <v-card flat>
                <v-card-text>
                  <v-text-field name="group[name]" v-model="data.name" outlined label="Название" placeholder="Название" :rules="rules.name"></v-text-field>
                  <v-text-field name="group[code]" v-model="data.code" outlined label="Code" placeholder="Code" :rules="rules.code" required></v-text-field>
                  <v-text-field name="group[sort]" v-model="data.sort" outlined label="Sort" placeholder="Sort" :type="'number'" :rules="rules.sort"></v-text-field>
                  <v-select name="group[user_id]" v-model="data.user_id" outlined :items="tutors" label="Ответственный"></v-select>
                  <v-select name="group[ou_id]" v-model="data.ou_id" outlined :items="ous" label="Подразделение" @change="loadGroups"></v-select>
                  <v-select name="group[parent_id]" v-model="data.parent_id" outlined :items="groups" label="Родительская группа"></v-select>
                  <v-textarea name="group[description]" v-model="data.description" outlined label="Описание" placeholder="Описание" :rules="rules.description"></v-textarea>
                  <v-checkbox name="group[active]" v-model="data.active" label="Активная"></v-checkbox>
                  <v-checkbox name="group[anonymous]" v-model="data.anonymous" label="Анонимная"></v-checkbox>
                  <v-datetime-picker
                      :text-field-props="textFieldProps('group[activeFrom]')"
                      :time-picker-props="timePickerProps"
                      timeFormat="HH:mm:ss"
                      v-model="data.activeFrom"
                      label="Активный с"
                      placeholder="Активный c"
                  > </v-datetime-picker>
                  <v-datetime-picker
                      :text-field-props="textFieldProps('group[activeTo]')"
                      :time-picker-props="timePickerProps"
                      timeFormat="HH:mm:ss"
                      v-model="data.activeTo"
                      label="Активный по"
                      placeholder="Активный по"
                  > </v-datetime-picker>
                </v-card-text>
              </v-card>
            </v-tab-item>
            <v-tab-item>
              <v-card flat>
                <v-card-text>
                  <v-select :items="users" label="Добавить" @change="addUser" ref="users"></v-select>
                  <v-simple-table>
                    <template v-slot:default>
                      <tbody>
                      <tr v-for="(item, index) in data.users" :key="index">
                        <td>
                          {{item.name}}
                          <input type="hidden" :name="'group[users]['+index+'][group_id]'" :value="item.group_id">
                          <input type="hidden" :name="'group[users]['+index+'][user_id]'" :value="item.user_id">
                        </td>
                        <td>
                          <v-datetime-picker
                              :text-field-props="textFieldProps('group[users]['+index+'][activeFrom]')"
                              :time-picker-props="timePickerProps"
                              timeFormat="HH:mm:ss"
                              v-model="item.activeFrom"
                              label="Активный с"
                              placeholder="Активный c"
                          > </v-datetime-picker>
                        </td>
                        <td>
                          <v-datetime-picker
                              :text-field-props="textFieldProps('group[users]['+index+'][activeTo]')"
                              :time-picker-props="timePickerProps"
                              timeFormat="HH:mm:ss"
                              v-model="item.activeTo"
                              label="Активный по"
                              placeholder="Активный по"
                          > </v-datetime-picker>
                        </td>
                        <td>
                          <v-btn small color="warning" title="Удалить" @click="removeUser(index)">
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
                            <input type="hidden" :name="'group[accesses]['+index+'][group_id]'" :value="item.group_id">
                            <input type="hidden" :name="'group[accesses]['+index+'][claimant_id]'" :value="item.claimant_id">
                          </td>
                          <td>
                            <v-text-field :name="'group[accesses]['+index+'][access]'" v-model="item.access" :type="'number'" label="Level"></v-text-field>
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
  name: "MainGroup",
  data: () => ({
    tutors: [],
    ous: [],
    groups: [],
    claimants: [],
    users: [],
    rules: {
      code: [
          v => !!v || 'Code is required',
          v => (v && v.length <= 255) || 'Code must be less than 255 characters',
      ],
      name: [
        v => !!v || 'Name is required',
      ],
      sort: [],
      description: []
    },
    oldName: null,
    data: {
      id: null,
      name: "",
      code: "",
      sort: 100,
      description: null,
      user_id: null,
      active: true,
      anonymous: false,
      ou_id: null,
      parent_id: null,
      x_timestamp: null,
      activeFrom: null,
      activeTo: null,
      accesses: {},
      users: {},
    },
    timePickerProps: {
      ampmInTitle: true,
      format: "24hr"
    },
  }),
  computed: {
    title () {
      if (this.data.id) {
        return 'Группа "' + this.oldName + '"';
      } else {
        return "Новая группа";
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
      formData.append('group[active]', this.data.active? 1: 0);
      formData.append('group[anonymous]', this.data.anonymous? 1: 0);
      if (!isNew) {
        formData.append('group[id]', this.data.id);
      }
      return this.$http.post('/main/group.php', formData).then((function (data) {
        if (isNew) {
          this.$store.dispatch('snackbar/message', 'Група "' + data.name + ' (' + data.code + ')", успешно создана!');
        } else {
          this.$store.dispatch('snackbar/message', 'Група "' + data.name + ' (' + data.code + ')", успешно изменена!');
        }
        this.oldName = this.data.name;
        if (back) {
          this.$router.go(-1);
        } else {
          this.data = data;
          if (!this.data.accesses) {
            this.$set(this.data, 'accesses', {})
          }
          if (!this.data.users) {
            this.$set(this.data, 'users', {})
          }
        }
      }).bind(this));
    },
    load () {
      const formData = new FormData();
      formData.append('f', "load");
      formData.append('group[code]', this.$route.params.code);
      return this.$http.post('/main/group.php', formData).then((function (data) {
        this.data = data;
        if (!this.data.accesses) {
          this.$set(this.data, 'accesses', {})
        }
        if (!this.data.users) {
          this.$set(this.data, 'users', {})
        }
        this.oldName = this.data.name;
      }).bind(this));
    },
    loadTutors () {
      const formData = new FormData();
      formData.append('f', "load-tutors");
      return this.$http.post('/main/group.php', formData).then((function (data) {
        this.tutors = data;
      }).bind(this));
    },
    loadUsers () {
      const formData = new FormData();
      formData.append('f', "load-users");
      return this.$http.post('/main/group.php', formData).then((function (data) {
        this.users = data;
      }).bind(this));
    },
    loadOUs () {
      const formData = new FormData();
      formData.append('f', "load-ous");
      return this.$http.post('/main/group.php', formData).then((function (data) {
        this.ous = data;
        this.loadGroups()
      }).bind(this));
    },
    loadGroups () {
      const formData = new FormData();
      formData.append('f', "load-groups");
      formData.append('ou_id', this.data.ou_id);
      formData.append('group_id', this.data.id);
      return this.$http.post('/main/group.php', formData).then((function (data) {
        this.groups = data;
      }).bind(this));
    },
    loadClaimants () {
      const formData = new FormData();
      formData.append('f', "load-claimants");
      return this.$http.post('/main/group.php', formData).then((function (data) {
        this.claimants = data;
      }).bind(this));
    },
    addAccess (val) {
      let notAppend = false;
      let addItem = {
        id: 'n'+(ii++),
        group_id: this.data.id,
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
    addUser (val) {
      let notAppend = false;
      let addItem = {
        id: 'n'+(ii++),
        group_id: this.data.id,
        user_id: val,
        name: "",
        activeFrom: null,
        activeTo: null,
      };
      this.users.forEach(function (item) {
        if (item.value == val) {
          addItem.name = item.text;
        }
      });
      Object.keys(this.data.users).forEach((function (key) {
        notAppend = notAppend || this.data.users[key].user_id == val;
      }).bind(this));
      if (notAppend) {
        this.$store.dispatch('snackbar/message', 'Нельзя добавить "' + addItem.name + '"! Уже есть!');
      } else {
        this.$set(this.data.users, addItem.id, addItem)
      }
      this.$refs.users.reset();
    },
    removeUser (val) {
      this.$delete(this.data.users, val);
    }
  },
  mounted () {
    if (this.$route.params.ou_id > 0) {
      this.data.ou_id = this.$route.params.ou_id;
    }
    this.load()
      .finally((function () {
        return this.loadTutors();
      }).bind(this)).finally((function () {
        return this.loadOUs();
      }).bind(this)).finally((function () {
        return this.loadClaimants();
      }).bind(this)).finally((function () {
        return this.loadUsers();
      }).bind(this));//*/
  }
}
</script>

<style scoped>

</style>
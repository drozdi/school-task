<template>
  <v-form ref="form">
    <v-card class="mx-auto">
      <v-card-title class="text-h5">
        {{ title }}
      </v-card-title>
      <v-card-text>
        <v-tabs background-color="primary" dark>
          <v-tab>
            Общее
          </v-tab>
          <v-tab>
            Предметы
          </v-tab>
          <v-tab-item>
            <v-card flat>
              <v-card-text>
                <v-row>
                  <v-col cols="12" md="6">
                    <input type="hidden" :name="'class[group_id]'" :value="data.group_id">
                    <v-text-field name="class[name]" v-model="data.name" outlined label="Название" placeholder="Название" :rules="rules.name" :disabled="!$store.getters['authentication/isRoot'] && !$store.getters['authentication/isClass']"></v-text-field>
                    <v-select name="class[parent_id]" v-model="data.parent_id" outlined label="Паралель" placeholder="Паралель" :items="parallels" :disabled="!$store.getters['authentication/isRoot'] && !$store.getters['authentication/isClass']"></v-select>
                    <v-select name="class[user_id]" v-model="data.user_id" outlined label="Классный руководитель" placeholder="Классный руководитель" :items="tutors" :disabled="!$store.getters['authentication/isRoot'] && !$store.getters['authentication/isClass']"></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select :items="pupils" label="Ученики" @change="addUser" ref="pupils"></v-select>
                    <v-simple-table>
                      <template v-slot:default>
                        <tbody>
                        <tr v-for="(item, index) in data.users" :key="index">
                          <td>
                            {{item.name}}
                            <input type="hidden" :name="'class[users]['+item.id+'][id]'" :value="item.id">
                            <input type="hidden" :name="'class[users]['+item.id+'][user_id]'" :value="item.user_id">
                            <input type="hidden" :name="'class[users]['+item.id+'][group_id]'" :value="item.group_id">
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
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-tab-item>
          <v-tab-item>
            <v-card flat>
              <v-card-text>
                <v-select :items="subjects" label="Предметы" @change="addSubject" ref="subjects"></v-select>
                <v-data-table
                    dense
                    :headers="[
                  { text: 'Название', value: 'name', sortable: false },
                  { text: 'Предмет', value: 'subject_id', sortable: false },
                  { text: 'Учитель', value: 'user_id', sortable: false },
                  { text: 'Actions', value: 'actions', sortable: false },
                ]"
                    :items="data.sub"
                    :itemsPerPage="-1"
                    item-key="id">
                  <template v-slot:[`item.name`]="{ item }">
                    <input type="hidden" :name="'class[sub]['+item.id+'][group_id]'" :value="item.group_id">
                    <div v-for="(subItem, index) in item.users" :key="index">
                      <input type="hidden" :name="'class[sub]['+item.id+'][users]['+subItem.id+'][id]'" :value="subItem.id">
                      <input type="hidden" :name="'class[sub]['+item.id+'][users]['+subItem.id+'][user_id]'" :value="subItem.user_id">
                      <input type="hidden" :name="'class[sub]['+item.id+'][users]['+subItem.id+'][group_id]'" :value="subItem.group_id">
                    </div>
                    <v-text-field :name="'class[sub]['+item.id+'][name]'" v-model="item.name" outlined label="Название" placeholder="Название"></v-text-field>
                  </template>
                  <template v-slot:[`item.subject_id`]="{ item }">
                    <span> {{item.subject_name}}</span>
                    <input type="hidden" :name="'class[sub]['+item.id+'][subject_id]'" :value="item.subject_id" />
                  </template>
                  <template v-slot:[`item.user_id`]="{ item }">
                    <v-select :name="'class[sub]['+item.id+'][user_id]'" v-model="item.user_id" outlined label="Учитель" placeholder="Учитель" :items="subjectTeachers(item.subject_id)"></v-select>
                  </template>
                  <template v-slot:[`item.actions`]="{ index }">
                    <v-btn small text class="mr-2" @click="changeSub(index)">
                      <v-icon>
                        mdi-pencil
                      </v-icon>
                    </v-btn>
                    <v-btn small text @click="removeSubject(index)">
                      <v-icon>
                        mdi-delete
                      </v-icon>
                    </v-btn>
                  </template>
                </v-data-table>
              </v-card-text>
            </v-card>
            <v-dialog v-model="dialog" max-width="700px">
              <v-card>
                <v-card-title>
                  <span class="text-h5">{{ editedItem.name }}</span>
                </v-card-title>
                <v-card-text>
                  <v-select :items="subUsers()" label="Ученики" @change="addSubUser" ref="subUsers"></v-select>
                  <v-simple-table>
                    <template v-slot:default>
                      <tbody>
                      <tr v-for="(item, index) in editedItem.users" :key="index">
                        <td>
                          {{item.name}}
                        </td>
                        <td>
                          <v-btn small color="warning" title="Удалить" @click="removeSubUser(index)">
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
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="blue darken-1" text @click="dialog = false">
                    Ok
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
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
  name: "EpClass",
  computed: {
    title () {
      if (this.data.id) {
        return 'Класс "'+this.oldName+'"'
      }
      return "Новый класс";
    }
  },
  data: () => ({
    dialog: false,
    parallels: [],
    tutors: [],
    pupils: [],
    subjects: [],
    rules: {
      name: [
        v => !!v || 'Name is required',
      ],
    },
    defaultItem: {
      id: null,
      name: "",
      subject_name: "",
      users: [],
      subject_id: null,
      parent_id: null,
      user_id: null
    },
    editedItem: {
      id: null,
      name: "",
      subject_name: "",
      users: [],
      subject_id: null,
      parent_id: null,
      user_id: null
    },
    data: {
      id: null,
      name: "",
      parent_id: null,
      user_id: null,
      users: [],
      sub: []
    }
  }),
  methods: {
    save (back) {
      const isNew = !this.data.id;
      const formData = new FormData(this.$refs.form.$el);
      formData.append('f', "save");
      if (!isNew) {
        formData.append('class[id]', this.data.id);
      }
      return this.$http.post('/ep/class.php', formData).then((function (data) {
        if (isNew) {
          this.$store.dispatch('snackbar/message', 'Класс "' + data.name + '", успешно создан!');
        } else {
          this.$store.dispatch('snackbar/message', 'Класс "' + data.name + '", успешно изменен!');
        }
        this.oldName = data.name;
        if (back) {
          this.$router.go(-1);
        } else {
          this.data = data;
          if (!this.data.users) {
            this.$set(this.data, 'users', [])
          }
          if (!this.data.sub) {
            this.$set(this.data, 'sub', [])
          }
        }
      }).bind(this));
    },
    load () {
      const formData = new FormData();
      formData.append('f', "load");
      formData.append('class[id]', this.$route.params.id);
      return this.$http.post('/ep/class.php', formData).then((function (data) {
        this.data = data;
        this.oldName = this.data.name;
        if (!this.data.users) {
          this.$set(this.data, 'users', [])
        }
        if (!this.data.sub) {
          this.$set(this.data, 'sub', [])
        }
      }).bind(this));
    },
    loadSubjects () {
      const formData = new FormData();
      formData.append('f', "load-subjects");
      return this.$http.post('/ep/class.php', formData).then((function (data) {
        this.subjects = data;
      }).bind(this));
    },
    loadParallels () {
      const formData = new FormData();
      formData.append('f', "load-parallels");
      return this.$http.post('/ep/class.php', formData).then((function (data) {
        this.parallels = data;
      }).bind(this));
    },
    loadTutors () {
      const formData = new FormData();
      formData.append('f', "load-tutors");
      return this.$http.post('/ep/class.php', formData).then((function (data) {
        this.tutors = data;
      }).bind(this));
    },
    loadPupils () {
      const formData = new FormData();
      formData.append('f', "load-pupils");
      return this.$http.post('/ep/class.php', formData).then((function (data) {
        this.pupils = data;
      }).bind(this));
    },

    subjectTeachers (subject_id) {
      for (let i = 0, cnt = this.subjects.length; i < cnt; i++) {
        if (this.subjects[i].value == subject_id) {
          return this.subjects[i].users;
        }
      }
      return [];
    },
    addUser (val) {
      let notAppend = false;
      let addItem = {
        id: 'n'+(ii++),
        user_id: val,
        group_id: this.data.id,
        name: "",
        text: ""
      };
      this.pupils.forEach(function (item) {
        if (item.value == val) {
          addItem.name = item.text;
        }
      });
      this.data.users.forEach(function (user) {
        notAppend = notAppend || user.user_id == val;
      });
      if (notAppend) {
        this.$store.dispatch('snackbar/message', 'Нельзя добавить "' + addItem.name + '"! Уже есть!');
      } else {
        this.data.users.push(addItem);
      }
      this.$refs.pupils.reset();
    },
    removeUser (index) {
      this.$delete(this.data.users, index);
    },
    addSubject (val) {
      let addItem = Object.assign({}, this.defaultItem);
      addItem.id = 'n'+(ii++);
      addItem.subject_id = val;
      addItem.parent_id = this.data.id;
      addItem.users = [];
      this.data.users.forEach(function (user) {
        let u = Object.assign({}, user);
        u.id = 'n'+(ii++);
        addItem.users.push(u);
      })
      this.subjects.forEach((function (item) {
        if (item.value == val) {
          addItem.subject_name = item.text;
          addItem.name = this.data.name + " " + item.text;
        }
      }).bind(this));
      this.data.sub.push(addItem);
      this.$refs.subjects.reset();
    },
    removeSubject (index) {
      this.$delete(this.data.sub, index);
    },
    changeSub (index) {
      this.editedItem = this.data.sub[index];
      this.dialog = true;
    },
    subUsers () {
      let res =[];
      this.data.users.forEach((function (user) {
        res.push({
          value: user.user_id,
          text: user.name
        })
      }).bind(this));
      return res;
    },
    addSubUser (val) {
      let notAppend = false;
      let addItem = {
        id: 'n'+(ii++),
        user_id: val,
        name: ""
      };
      this.subUsers().forEach(function (item) {
        if (item.value == val) {
          addItem.name = item.text;
        }
      });
      this.editedItem.users.forEach(function (user) {
        notAppend = notAppend || user.user_id == val;
      });
      if (notAppend) {
        this.$store.dispatch('snackbar/message', 'Нельзя добавить "' + addItem.name + '"! Уже есть!');
      } else {
        this.editedItem.users.push(addItem);
      }
      this.$refs.subUsers.reset();
    },
    removeSubUser (index) {
      this.$delete(this.editedItem.users, index);
    },
  },
  mounted () {
    this.loadSubjects()
        .finally((function () {
          return this.loadParallels();
        }).bind(this))
        .finally((function () {
          return this.loadTutors();
        }).bind(this))
        .finally((function () {
          return this.loadPupils();
        }).bind(this))
        .finally((function () {
          return this.load();
        }).bind(this));
  }
}
</script>

<style scoped>

</style>
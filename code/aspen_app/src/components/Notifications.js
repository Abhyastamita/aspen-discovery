import Constants from 'expo-constants';
import * as Device from 'expo-device';
import * as Notifications from 'expo-notifications';
import React from 'react';
import { Platform, View } from 'react-native';
import { Alert, Button, HStack, Text, Center } from 'native-base';
import { create } from 'apisauce';

// custom components and helper files
import { createAuthTokens, getHeaders, postData, problemCodeMap, stripHTML } from '../util/apiAuth';
import { GLOBALS } from '../util/globals';
import { popAlert, popToast } from './loadError';

export async function registerForPushNotificationsAsync(url) {
     console.log('url: ' + url);
     let token = false;
     if (Device.isDevice) {
          if (Platform.OS === 'android') {
               await createChannelsAndCategories();
          }
          const { status: existingStatus } = await Notifications.getPermissionsAsync();
          console.log('status: ' + existingStatus);
          let finalStatus = existingStatus;
          if (existingStatus !== 'granted') {
               if (Platform.OS !== 'android') {
                    const { status } = await Notifications.requestPermissionsAsync();
                    finalStatus = status;
               }
          }
          if (finalStatus !== 'granted') {
               console.log('Failed to get push token for push notification!');
               return;
          }
          token = (await Notifications.getExpoPushTokenAsync()).data;
          console.log('token: ' + token);
          if (token) {
               await savePushToken(url, token);
          }
     } else {
          alert('Push notifications require a physical device');
     }

     return token;
}

export async function savePushToken(url, pushToken) {
     let postBody = await postData();
     postBody.append('pushToken', pushToken);
     postBody.append('deviceModel', Device.modelName);
     const api = create({
          baseURL: url + '/API',
          timeout: GLOBALS.timeoutAverage,
          headers: getHeaders(true),
          auth: createAuthTokens(),
     });
     const response = await api.post('/UserAPI?method=saveNotificationPushToken', postBody);
     console.log('saveNotificationPushToken: ' + response);
     if (response.ok) {
          if (response.data.result.success) {
               popAlert(response.data.result.title, response.data.result.message, 'success');
          } else {
               popAlert(response.data.result.title, response.data.result.message, 'error');
          }
     } else {
          const problem = problemCodeMap(response.problem);
          popToast(problem.title, problem.message, 'warning');
          console.log(response);
     }
}

export async function getPushToken(libraryUrl) {
     let postBody = await postData();
     const api = create({
          baseURL: libraryUrl + '/API',
          timeout: GLOBALS.timeoutAverage,
          headers: getHeaders(true),
          auth: createAuthTokens(),
     });
     const response = await api.post('/UserAPI?method=getNotificationPushToken', postBody);
     if (response.ok) {
          if (response.data.result.success) {
               return response.data.result.tokens;
          } else {
               return [];
          }
     } else {
          console.log(response);
          return [];
     }
}

export async function deletePushToken(libraryUrl, pushToken, shouldAlert = false) {
     let postBody = await postData();
     postBody.append('pushToken', pushToken);
     const api = create({
          baseURL: libraryUrl + '/API',
          timeout: GLOBALS.timeoutAverage,
          headers: getHeaders(true),
          auth: createAuthTokens(),
     });
     const response = await api.post('/UserAPI?method=deleteNotificationPushToken', postBody);
     if (response.ok) {
          //console.log(response);
          if (shouldAlert) {
               if (response.data.result.success) {
                    popAlert(response.data.result.title, response.data.result.message, 'success');
               } else {
                    popAlert(response.data.result.title, response.data.result.message, 'error');
               }
          }
          return true;
     } else {
          const problem = problemCodeMap(response.problem);
          popToast(problem.title, problem.message, 'warning');
          console.log(response);
          return false;
     }
}

async function createNotificationChannelGroup(id, name, description = null) {
     if (Platform.OS === 'android') {
          Notifications.setNotificationChannelGroupAsync(`${id}`, {
               name: `${name}`,
               description: `${description}`,
          });
     }
}

async function getNotificationChannelGroup(group) {
     if (Platform.OS === 'android') {
          return Notifications.getNotificationChannelGroupAsync(`${group}`);
     }
     return false;
}

async function createNotificationChannel(id, name, groupId) {
     if (Platform.OS === 'android') {
          Notifications.setNotificationChannelAsync(`${id}`, {
               name: `${name}`,
               importance: Notifications.AndroidImportance.MAX,
               vibrationPattern: [0, 250, 250, 250],
               lightColor: '#FF231F7C',
               groupId: `${groupId}`,
               showBadge: true,
          });
     }
}

async function getNotificationChannel(channel) {
     if (Platform.OS === 'android') {
          return Notifications.getNotificationChannelAsync(`${channel}`);
     }
     return false;
}

async function deleteNotificationChannel(channel) {
     if (Platform.OS === 'android') {
          return Notifications.deleteNotificationChannelAsync(`${channel}`);
     }
     return false;
}

async function createNotificationCategory(id, name, button) {
     Notifications.setNotificationCategoryAsync(`${id}`, [
          {
               identifier: `${name}`,
               buttonTitle: `${button}`,
          },
     ]);
}

async function getNotificationCategory(category) {
     return Notifications.getNotificationCategoriesAsync();
}

async function deleteNotificationCategory(category) {
     return Notifications.deleteNotificationCategoryAsync(`${category}`);
}

export async function getNotificationPreferences(libraryUrl, pushToken) {
     let postBody = await postData();
     postBody.append('pushToken', pushToken);
     const api = create({
          baseURL: libraryUrl + '/API',
          timeout: GLOBALS.timeoutAverage,
          headers: getHeaders(true),
          auth: createAuthTokens(),
     });
     const response = await api.post('/UserAPI?method=getNotificationPreferences', postBody);
     if (response.ok) {
          try {
               await createChannelsAndCategories();
          } catch (e) {
               console.log(e);
          }
          return response.data.result;
     } else {
          const problem = problemCodeMap(response.problem);
          popToast(problem.title, problem.message, 'warning');
          console.log(response);
          return false;
     }
}

export async function getNotificationPreference(url, pushToken, type) {
     let postBody = await postData();
     postBody.append('pushToken', pushToken);
     postBody.append('type', type);
     const api = create({
          baseURL: url + '/API',
          timeout: GLOBALS.timeoutAverage,
          headers: getHeaders(true),
          auth: createAuthTokens(),
          params: {
               type: type,
          },
     });
     const response = await api.post('/UserAPI?method=getNotificationPreference', postBody);
     if (response.ok) {
          if (response.data.result.success === true) {
               return response.data.result;
          } else {
               popAlert(response.data.result.title ?? 'Unknown Error', response.data.result.message, 'error');
               return false;
          }
     } else {
          const problem = problemCodeMap(response.problem);
          popToast(problem.title, problem.message, 'warning');
          //console.log(response);
          return false;
     }
}

export async function setNotificationPreference(url, pushToken, type, value, showToast = true) {
     let postBody = await postData();
     postBody.append('pushToken', pushToken);
     postBody.append('type', type);
     postBody.append('value', value);
     const api = create({
          baseURL: url + '/API',
          timeout: GLOBALS.timeoutAverage,
          headers: getHeaders(true),
          auth: createAuthTokens(),
          params: {
               type: type,
               value: value,
          },
     });
     const response = await api.post('/UserAPI?method=setNotificationPreference', postBody);
     if (response.ok) {
          if (response.data.result.success === true) {
               if (showToast) {
                    popAlert(response.data.result.title, response.data.result.message, 'success');
               }
               return response.data.result;
          } else {
               if (showToast) {
                    popAlert(response.data.result.title ?? 'Unknown Error', response.data.result.message, 'error');
               }
               return false;
          }
     } else {
          const problem = problemCodeMap(response.problem);
          if (showToast) {
               popToast(problem.title, problem.message, 'warning');
          }
          return false;
     }
}

export async function createChannelsAndCategories() {
     const updatesChannelGroup = await getNotificationChannelGroup('updates');
     if (!updatesChannelGroup) {
          await createNotificationChannelGroup('updates', 'Updates');
     }

     const savedSearchChannel = await getNotificationChannel('savedSearch');
     if (!savedSearchChannel) {
          await createNotificationChannel('savedSearch', 'Saved Searches', 'updates');
     }

     const libraryAlertChannel = await getNotificationChannel('libraryAlert');
     if (!libraryAlertChannel) {
          await createNotificationChannel('libraryAlert', 'Library Alert', 'updates');
     }

     const accountAlertChannel = await getNotificationChannel('accountAlert');
     if (!accountAlertChannel) {
          await createNotificationChannel('accountAlert', 'Account Alert', 'updates');
     }

     const savedSearchCategory = await getNotificationCategory('savedSearch');
     if (!savedSearchCategory) {
          await createNotificationCategory('savedSearch', 'Saved Searches', 'View');
     }

     const libraryAlertCategory = await getNotificationCategory('libraryAlert');
     if (!libraryAlertCategory) {
          await createNotificationCategory('libraryAlert', 'Library Alert', 'Read More');
     }

     const accountAlertCategory = await getNotificationCategory('accountAlert');
     if (!accountAlertCategory) {
          await createNotificationCategory('accountAlert', 'Account Alert', 'View');
     }
}

/** status/colorScheme options: success, error, info, warning **/
export function showILSMessage(type, message, index = 0) {
     const formattedMessage = stripHTML(message);
     return (
          <Alert maxW="95%" status={type} colorScheme={type} mb={1} ml={2} key={index}>
               <HStack flexShrink={1} space={2} alignItems="center" justifyContent="space-between">
                    <HStack flexShrink={1} space={2} alignItems="center">
                         <Alert.Icon />
                         <Text fontSize="xs" fontWeight="medium" color="coolGray.800" maxW="90%">
                              {formattedMessage}
                         </Text>
                    </HStack>
               </HStack>
          </Alert>
     );
}

/** status/colorScheme options: success, error, info, warning **/
export const DisplayMessage = (props) => {
     return (
          <Alert status={props.type} colorScheme={props.type} mb={2}>
               <HStack flexShrink={1} space={5} alignItems="center" justifyContent="space-between" px={4}>
                    <Alert.Icon />
                    <Text fontSize="xs" fontWeight="medium" color="coolGray.800">
                         {props.message}
                    </Text>
               </HStack>
          </Alert>
     );
};